<?php

if (!defined('sugarEntry'))
    define('sugarEntry', true);

chdir("../");
require_once('include/entryPoint.php');
session_start();

$authController = new AuthenticationController();
if (isset($_SESSION['authenticated_user_id'])) {
    if (!$authController->sessionAuthenticate()) {
        session_destroy();
        indexRedirect(array('module' => 'Users', 'action' => 'Login'));
    }
    $GLOBALS['log']->debug('Current user is: ' . $current_user->user_name);
    $current_user->update_access_time();
}

if (!$cti_user = $current_user->cti_user_id)
    return;

$query = "
	SELECT * FROM cti_call log
	where
		ID = (
			SELECT id FROM cti_call sub
			WHERE sub.cti_id = log.cti_id
			order by timestamp desc
			limit 1
		)
	AND
		state <> 'HANGUP'
	AND 
		cti_user = '$cti_user' 
	";


$resultSet = $current_user->db->query($query, true);
if ($current_user->db->checkError()) {
    trigger_error("checkForNewStates-Query failed");
}
//if ($resultSet->num_rows == 0) return; //no call

$response = array();

require_once 'include/application/ModuleController.php';
$current_language = ModuleController::get_user_language();

$mod_strings = AppConfig::setting("lang.strings.current.CTI");
while ($row = $current_user->db->fetchByAssoc($resultSet)) {

    $item = array();
    $item['cti_id'] = $row['cti_id'];
    $item['state'] = isset($mod_strings[$row['state']]) ? $mod_strings[$row['state']] : $row['state'];

    $query2 = "
	SELECT * FROM cti_call log
	where
	cti_id = '$row[cti_id]'
	order by timestamp desc
	";

    $innerResultSet = $current_user->db->query($query2, false);
    if ($current_user->db->checkError()) {
        trigger_error("checkForNewStates-Query failed");
    }
    $callStates = array();
    while ($innerRow = $current_user->db->fetchByAssoc($innerResultSet)) {

        $callStates[] = $innerRow['state'];

    }

    if (in_array("REQUESTED", $callStates) || in_array("PROCEEDING", $callStates) || in_array("RINGBACK", $callStates)) {

        // this one is a call initiated by the user himself
        $item['call_type'] = "SFLBL_GOING_OUT";
        $item['direction'] = "Outbound";

        $item['phone_number'] = $row['called_number'];
        $item['sf_name'] = $row['called_name'];
    } elseif (in_array("RINGING", $callStates) || in_array("INCOMING", $callStates)) {

        // this call is coming in from a remote phone partner
        $item['call_type'] = "SFLBL_COMING_IN";
        $item['direction'] = "Inbound";
        $item['phone_number'] = $row['caller_number'];
        $item['sf_name'] = $row['caller_name'];
    }
    $item['cti_id'] = $row['cti_id'];

    // dont search for first three chars : +49,
    // other chars like ' ', '-' are taken away in the query
    $phoneToFind = $item['phone_number'];
    if (strlen($phoneToFind) > 4) {
        $phoneToFind = substr($phoneToFind, 3);
    }
    $found = array();
    if ($phoneToFind) {
        $sqlReplace = "
			    replace(
			    replace(
			    replace(
			    replace(
			    replace(
			    replace(
			    replace(
			    replace(
			      %s, 
			        ' ', ''), 
			        '+', ''), 
			        '/', ''), 
			        '(', ''), 
			        ')', ''), 
			        '[', ''), 
			        ']', ''), 
			        '-', '') 
			        LIKE '%%%s'
			";


        $queryContact = "SELECT
					c.id as contact_id, 
					first_name,
					last_name,
					phone_work,
					phone_home,
					phone_mobile,
					phone_other,
					a.name as account_name,
					account_id
					 
				FROM contacts c
				left join accounts_contacts ac on (c.id=ac.contact_id AND ac.deleted=0)
				left join accounts a on (ac.account_id=a.id AND a.deleted=0)
				WHERE c.deleted=0 AND (";
        $queryContact .= sprintf($sqlReplace, "phone_work", $phoneToFind) . " OR ";
        $queryContact .= sprintf($sqlReplace, "phone_home", $phoneToFind) . " OR ";
        $queryContact .= sprintf($sqlReplace, "phone_other", $phoneToFind) . " OR ";
        $queryContact .= sprintf($sqlReplace, "assistant_phone", $phoneToFind) . " OR ";
        $queryContact .= sprintf($sqlReplace, "phone_mobile", $phoneToFind) . ")";

        $innerResultSet = $current_user->db->query($queryContact, false);
        if ($current_user->db->checkError()) {
            StarfaceHelper::log("checkForNewStates :: query failed: " . $queryContact, "checkForNewStates.log");
        }
        $found['contacts'] = array();
        while ($contactRow = $current_user->db->fetchByAssoc($innerResultSet)) {

            $found['contacts'][] = array('$contactFullName' => $contactRow['first_name'] . " " . $contactRow['last_name'],
                '$company' => $contactRow['account_name'],
                '$contactId' => $contactRow['contact_id'],
                '$companyId' => $contactRow['account_id']);
        }

        $queryAccount = "SELECT
				a.id as account_id, 
				a.name  as account_name
				FROM accounts a
				WHERE a.deleted=0 AND (";
        $queryAccount .= sprintf($sqlReplace, "phone_office", $phoneToFind) . " OR ";
        $queryAccount .= sprintf($sqlReplace, "phone_alternate", $phoneToFind) . ")";

        $innerResultSet = $current_user->db->query($queryAccount, false);
        if ($current_user->db->checkError()) {
            trigger_error("checkForNewStates :: query failed: " . $queryAccount, "checkForNewStates.log");
        }
        $found['accounts'] = array();
        while ($contactRow = $current_user->db->fetchByAssoc($innerResultSet)) {

            $found['accounts'][] = array( //'$contactFullName' => $contactRow['first_name'] . " " . $contactRow['last_name'],
                '$company' => $contactRow['account_name'],
                //'$contactId' => $contactRow['contact_id'],
                '$companyId' => $contactRow['account_id']);
        }


        $queryLead = "SELECT
					id as lead_id, 
					first_name,
					last_name,
					account_name
				FROM leads
				WHERE deleted=0 AND converted=0 AND (";
        $queryLead .= sprintf($sqlReplace, "phone_home", $phoneToFind) . " OR ";
        $queryLead .= sprintf($sqlReplace, "phone_mobile", $phoneToFind) . " OR ";
        $queryLead .= sprintf($sqlReplace, "phone_work", $phoneToFind) . " OR ";
        $queryLead .= sprintf($sqlReplace, "phone_other", $phoneToFind) . ")";

        $innerResultSet = $current_user->db->query($queryLead, false);
        if ($current_user->db->checkError()) {
            trigger_error("checkForNewStates :: query failed: " . $queryLead);
        }
        $found['leads'] = array();
        while ($contactRow = $current_user->db->fetchByAssoc($innerResultSet)) {

            $found['leads'][] = array('$contactFullName' => $contactRow['first_name'] . " " . $contactRow['last_name'],
                '$company' => $contactRow['account_name'],
                '$contactId' => $contactRow['lead_id']);
        }

        if (count($found['accounts'])) {
            //$item['full_name'] = isset($found['accounts'][0]['$contactFullName']) ? $found['accounts'][0]['$contactFullName'] : "";
            $item['company'] = isset($found['accounts'][0]['$company']) ? $found['accounts'][0]['$company'] : "";
            //$item['contact_id'] = isset($found['accounts'][0]['$contactId']) ? $found['accounts'][0]['$contactId'] : "";
            $item['company_id'] = isset($found['accounts'][0]['$companyId']) ? $found['accounts'][0]['$companyId'] : "";
        }

        if (count($found['contacts'])) {
            $item['full_name'] = isset($found['contacts'][0]['$contactFullName']) ? $found['contacts'][0]['$contactFullName'] : "";
            $item['contact_id'] = isset($found['contacts'][0]['$contactId']) ? $found['contacts'][0]['$contactId'] : "";
            if (!count($found['accounts'])) {
                $item['company'] = isset($found['contacts'][0]['$company']) ? $found['contacts'][0]['$company'] : "";
                $item['company_id'] = isset($found['contacts'][0]['$companyId']) ? $found['contacts'][0]['$companyId'] : "";
            }
        }

        if (count($found['leads']) && !count($found['accounts']) && !count($found['contacts'])) {
            $item['full_name'] = isset($found['leads'][0]['$contactFullName']) ? $found['leads'][0]['$contactFullName'] : "";
            $item['company'] = isset($found['leads'][0]['$company']) ? $found['leads'][0]['$company'] : "";
            $item['contact_id'] = isset($found['leads'][0]['$contactId']) ? $found['leads'][0]['$contactId'] : "";
            $item['company_id'] = "";
        }
        $item['found'] = $found;
        $response[] = $item;
    }

}

//uncomment for test-data
//$response[] = array('cti_id' => '111111', 'state' => 'Ringing', 'full_name' => 'Mr. Test', 'company' => 'TestFirma', 'direction' => "Outbound", 'call_type' => "SFLBL_GOING_OUT", 'phone_number' => '+49 711 6491238', 'sf_name' => 'caller_name', 'contact_id' => '', 'found' => array());
//$response[] = array('cti_id' => '222222', 'state' => 'RINGING', 'full_name' => 'Mr. Test', 'company' => 'TestFirma', 'direction' => "Outbound", 'call_type' => "SFLBL_GOING_OUT", 'phone_number' => '11111111111111111', 'sf_name' => 'caller_name', 'contact_id' => '23452345', 'found' => array('contacts' => array(12234)));


$responseArray = array();
require_once('XTemplate/xtpl.php');

foreach ($response as $item) {
    $xtpl = new XTemplate('cti/checkForNewStates.tpl');
    $xtpl->assign("MOD", $mod_strings);
    $xtpl->assign("CURRENT_USER_ID", $currentUserObj->id);
    $xtpl->assign("CTI_ID", $item['cti_id']);
    $xtpl->assign("STATE", $item['state']);
    $xtpl->assign("PHONE_NUMBER", $item['phone_number']);
    $xtpl->assign("PHONE_NUMBER_URL", urlencode($item['phone_number']));
    $xtpl->assign("CONTACT_ID", $item['contact_id']);
    $xtpl->assign("LEAD_ID", empty($item['found']['leads'][0]['$contactId']) ? '' : $item['found']['leads'][0]['$contactId']);


    $add_note_link = '';
    if (!empty($item['company_id'])) {
        $add_note_link .= '&parent_id=' . $item['company_id'] . '&parent_type=Accounts&parent_name=' . urlencode($item['company']);
    } elseif (!empty($item['contact_id']) && count($item['found']['contacts']) && $item['contact_id'] == $item['found']['contacts'][0]['$contactId']) {
        $add_note_link .= '&parent_id=' . $item['found']['contacts'][0]['$contactId'] . '&parent_type=Contacts&parent_name=' . urlencode($item['found']['contacts'][0]['$contactFullName']);
    } elseif (count($item['found']['leads']) && $item['found']['leads'][0]['$contactId'] == $item['contact_id']) {
        $add_note_link .= '&parent_id=' . $item['found']['leads'][0]['$contactId'] . '&parent_type=Leads&parent_name=' . urlencode($item['found']['leads'][0]['$contactFullName']);
    }

    $add_note_link .= '&direction=' . $item['direction'];

    if (count($item['found']['leads']) || count($item['found']['accounts']) || count($item['found']['contacts'])) {
        $xtpl->assign("PHONE_NUMBER_SEPARATOR", ',');
    }

    $xtpl->assign("ADD_NOTE_LINK", $add_note_link);
    $xtpl->assign("FULL_NAME", $item['full_name']);
    $xtpl->assign("COMPANY", $item['company']);
    $xtpl->assign("COMPANY_ID", $item['company_id']);

    if (!empty($item['company_id'])) {
        $xtpl->assign("COMPANY_LINK", '&module=Accounts&record=' . $item['company_id']);
        $xtpl->assign("COMPANY_LINK_TITLE", $mod_strings['SFLBL_COMPANY']);
        $xtpl->parse("call.company_link");
    } elseif (!empty($item['company']) && !empty($item['found']['leads'][0]['$contactId'])) {
        $xtpl->assign("COMPANY_LINK", '&module=Leads&record=' . $item['found']['leads'][0]['$contactId']);
        $xtpl->assign("COMPANY_LINK_TITLE", $mod_strings['SFLBL_LEAD']);
        $xtpl->parse("call.company_link");
    }

    if (count($item['found']['contacts']) && $item['contact_id'] == $item['found']['contacts'][0]['$contactId']) {
        $xtpl->parse("call.contact_link");
    } elseif (count($item['found']['leads']) && $item['contact_id'] == $item['found']['leads'][0]['$contactId']) {
        $xtpl->parse("call.lead_link");
    }

    $xtpl->assign("CALL_TYPE", $mod_strings[$item['call_type']]);
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

    if (empty($item['contact_id']) || !count($item['found']['leads'])) {
        $xtpl->parse("call.lead_add");
    } elseif (count($item['found']['leads']) && $item['found']['leads'][0]['$contactId'] == $item['contact_id']) {
        $xtpl->parse("call.lead_exists_show");
    } else
        $xtpl->parse("call.lead_exists");

    $xtpl->parse("call" );
    $item['html'] = str_replace(array("\r", "\t", "\n"), "", /* '<pre>'.nl2br(print_r($item,1)).'</pre>'. */
        $xtpl->text("call"));
    unset($item['found']);

    $responseArray[] = $item;
}
echo json_encode($responseArray);

sugar_cleanup();
