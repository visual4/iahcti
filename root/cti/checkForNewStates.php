<?php
if (!defined('sugarEntry'))
    define('sugarEntry', true);

chdir("../");
require_once('include/entryPoint.php');
require_once __DIR__ . '/../modules/CTI/include/StarfaceHelper.class.php';
if (!AppConfig::session_start()) {
    header('HTTP/1.1 503 Service Unavailable');
    sugar_die('Error starting session');
}

$authController = new AuthenticationController();
if (isset($_SESSION['authenticated_user_id'])) {
    if (!$authController->sessionAuthenticate()) {
        session_destroy();
        indexRedirect(array('module' => 'Users', 'action' => 'Login'));
    }
    $GLOBALS['log']->debug('Current user is: ' . $current_user->user_name);
    $current_user->update_access_time();
}

if (!isset($current_user->cti_user_id))
    return;

$cti_user = $current_user->cti_user_id;

$lq = new ListQuery('ctiCall', [
    'contact',
    'lead',
    'lookup_number',
    'cti_id',
    'direction',
    'state',
    'contact.primary_account',
    'contact.primary_account.temperature',
    'contact.primary_account.balance'

]);
$lq->addSimpleFilter('state', 'HANGUP', 'not_eq');
$lq->addSimpleFilter('cti_user', $cti_user);
$calls = $lq->runQuery(0, 5, false, 'date_modified desc')->getRowResults();

//var_dump($calls);

$response = array();

require_once 'include/application/ModuleController.php';

$mod_strings = AppConfig::setting("lang.strings.current.CTI");
$minDigits = AppConfig::setting('cti.internal_digits', 0);
foreach ($calls as $row) {
    //error_log(print_r($row, 1));
    $item = array();
    $item['cti_id'] = $row->getField('cti_id');
    $item['state'] = translate('cti_call_states_dom', 'CTI', $row->getField('state'));


    $item['phone_number'] = $row->getField('lookup_number');
    if ($row->getField('direction') == 'OUT') {

        // this one is a call initiated by the user himself
        $item['call_type'] = "SFLBL_GOING_OUT";
        $item['direction'] = "Outbound";

    } else {
        // this call is coming in from a remote phone partner
        $item['call_type'] = "SFLBL_COMING_IN";
        $item['direction'] = "Inbound";

    }
    $item['cti_id'] = $row->getField('cti_id');
    $contact = $row->getField('contact');

    $item['full_name'] = $contact;
    $item['contact_id'] = $row->getField('contact_id');
    $item['temperature'] = translate('temperature_dom', 'CTI', $row->getField('contact.primary_account.temperature'));
    $item['balance'] = $row->getField('contact.primary_account.balance');


    $item['company'] = $row->getField('contact.primary_account');
    $item['company_id'] = $row->getField('contact.primary_account_id');;

    $lead = $row->getField('lead');
    if (empty($contact) && !empty($lead)) {
        $item['full_name'] = $lead;
        $item['company'] = isset($found['leads'][0]['$company']) ? $found['leads'][0]['$company'] : "";
        $item['contact_id'] = isset($found['leads'][0]['$contactId']) ? $found['leads'][0]['$contactId'] : "";
        $item['company_id'] = "";
    }

    $item['found'] = [
        'contacts' => [
            ['$contactId' => $row->getField('contact_id')]
        ],


    ];
    $response[] = $item;


}


$displayTest = AppConfig::setting('cti.displaytest', false);
if ($displayTest) {
    $response[] = array(
        'cti_id' => '223222',
        'state' => 'RINGING',
        'full_name' => 'Mr. Test',
        'company' => 'TestFirma',
        'direction' => "Outbound",
        'call_type' => "SFLBL_GOING_OUT",
        'phone_number' => '11111111111111111',
        'sf_name' => 'caller_name',
        'company_id' => '998ba980-e56e-cf24-b9ec-549150439f4c',
        'contact_id' => '41f8ebe8-925d-74b2-acb1-558a9e6dcc52',
        'balance' => '500',
        'temperature' => 'AA',
        'found' => array(
            'contacts' => array(array('$contactId' => '41f8ebe8-925d-74b2-acb1-558a9e6dcc52')),
            'accounts' => array('998ba980-e56e-cf24-b9ec-549150439f4c'),
            'leads' => array(),
        )
    );
}


$responseArray = array();
require_once('XTemplate/xtpl.php');

foreach ($response as $item) {
    $xtpl = new XTemplate('cti/checkForNewStates.tpl');
    $xtpl->assign("MOD", $mod_strings);
    $xtpl->assign("CURRENT_USER_ID", $current_user->id);
    $xtpl->assign("CTI_ID", $item['cti_id']);
    $xtpl->assign("STATE", $item['state']);
    $xtpl->assign("PHONE_NUMBER", $item['phone_number']);
    $xtpl->assign("PHONE_NUMBER_URL", urlencode($item['phone_number']));
    $xtpl->assign("CONTACT_ID", array_get_default($item, 'contact_id', ''));
    $xtpl->assign("LEAD_ID", empty($item['found']['leads'][0]['$contactId']) ? '' : $item['found']['leads'][0]['$contactId']);


    $add_note_link = '';
    if (!empty($item['company_id'])) {
        $item['parent_type'] = 'Accounts';
        $add_note_link .= '&parent_id=' . $item['company_id'] . '&parent_type=Accounts&parent_name=' . urlencode($item['company']);
    } elseif (!empty($item['contact_id']) && count($item['found']['contacts']) && $item['contact_id'] == $item['found']['contacts'][0]['$contactId']) {
        $item['parent_type'] = 'Contacts';
        $add_note_link .= '&parent_id=' . $item['found']['contacts'][0]['$contactId'] . '&parent_type=Contacts&parent_name=' . urlencode($item['found']['contacts'][0]['$contactFullName']);
    } elseif (count($item['found']['leads']) && $item['found']['leads'][0]['$contactId'] == $item['contact_id']) {
        $item['parent_type'] = 'Leads';
        $add_note_link .= '&parent_id=' . $item['found']['leads'][0]['$contactId'] . '&parent_type=Leads&parent_name=' . urlencode($item['found']['leads'][0]['$contactFullName']);
    }

    $add_note_link .= '&direction=' . $item['direction'];

    $item['add_note_link'] = $add_note_link;

    if (count($item['found']['leads']) || count($item['found']['accounts']) || count($item['found']['contacts'])) {
        $xtpl->assign("PHONE_NUMBER_SEPARATOR", ',');
    }

    $xtpl->assign("ADD_NOTE_LINK", $add_note_link);

    $add_case_link = '';
    $add_case_link .= '&cust_phone_no=' . $item['phone_number'];
    $not_empty = !empty($item['company_id']);
    $count = count($item['found']['accounts']);
    $same = $item['company_id'] == $item['found']['accounts'][0];
    if (!empty($item['company_id']) && count($item['found']['accounts']) && $item['company_id'] == $item['found']['accounts'][0]) {
        $add_case_link .= '&primary_name=account_id&account_id=' . $item['company_id'];
    }
    if (!empty($item['contact_id']) && count($item['found']['contacts']) && $item['contact_id'] == $item['found']['contacts'][0]['$contactId']) {
        $add_case_link .= '&contact_id=' . $item['found']['contacts'][0]['$contactId'];
    }

    $item['add_case_link'] = $add_case_link;
    $xtpl->assign("ADD_CASE_LINK", $add_case_link);


    $xtpl->assign("FULL_NAME", array_get_default($item, 'full_name', ''));
    $xtpl->assign("COMPANY", array_get_default($item, 'company', ''));
    $xtpl->assign("COMPANY_ID", array_get_default($item, 'company_id', ''));
    $item['company_label'] = $mod_strings['SFLBL_COMPANY'];
    if (!empty($item['balance'])) $xtpl->assign('BALANCE', currency_format_number(-1 * $item['balance']));
    if (!empty($item['temperature'])) $xtpl->assign('TEMPERATURE', $item['temperature']);

    if (!empty($item['company_id'])) {
        $xtpl->assign("COMPANY_LINK", '&module=Accounts&record=' . $item['company_id']);
        $xtpl->assign("COMPANY_LINK_TITLE", $mod_strings['SFLBL_COMPANY']);
        $xtpl->parse("call.company_link");


    } elseif (!empty($item['company']) && !empty($item['found']['leads'][0]['$contactId'])) {
        $xtpl->assign("COMPANY_LINK", '&module=Leads&record=' . $item['found']['leads'][0]['$contactId']);
        $xtpl->assign("COMPANY_LINK_TITLE", $mod_strings['SFLBL_LEAD']);
        $xtpl->parse("call.company_link");
        $item['company_label'] = $mod_strings['SFLBL_LEAD'];
    }

    if (count($item['found']['contacts']) && $item['contact_id'] == $item['found']['contacts'][0]['$contactId']) {
        $xtpl->parse("call.contact_link");
    } elseif (count($item['found']['leads']) && $item['contact_id'] == $item['found']['leads'][0]['$contactId']) {
        $xtpl->parse("call.lead_link");
    }

    $item['call_type'] = $mod_strings[$item['call_type']];
    $xtpl->assign("CALL_TYPE", $item['call_type']);


    if (count($responseArray) < 2)
        $xtpl->parse("call.call_settings");
    if (!empty($item['company_id'])) {
        $xtpl->parse("call.account_exists_show");
    } else
        $xtpl->parse("call.account_add");

    if (!empty($item['contact_id']) && count($item['found']['contacts'])) {
        $xtpl->parse("call.contact_exists_show");
    } else
        $xtpl->parse("call.contact_add");

    $emptyContact = empty($item['contact_id']);
    $noLead = !count($item['found']['leads']);
    if ($emptyContact || $noLead) {
        $xtpl->parse("call.lead_add");
    } elseif (!$noLead && $item['found']['leads'][0]['$contactId'] == $item['contact_id']) {
        $xtpl->parse("call.lead_exists_show");
    } else
        $xtpl->parse("call.lead_exists");

    $xtpl->parse("call");
    $item['html'] = str_replace(array("\r", "\t", "\n"), "", /* '<pre>'.nl2br(print_r($item,1)).'</pre>'. */
        $xtpl->text("call"));
    unset($item['found']);

    $item['name_label'] = $mod_strings['SFLBL_NAME'];
    $item['phone_label'] = $mod_strings['SFLBL_PHONE'];

    $responseArray[] = $item;
}
echo json_encode($responseArray);

sugar_cleanup();
