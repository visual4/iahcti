<?php
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 13.02.2017
 * Time: 19:28
 */
error_log('Lookup -' . $_REQUEST['q'] . '-');
header('Content-Type: application/json');
if (!defined('sugarEntry'))
    define('sugarEntry', true);

chdir("../");

require_once('include/entryPoint.php');
require_once 'modules/CTI/v4_crm_crypt.php';

if (!AppConfig::session_start()) {
    header('HTTP/1.1 503 Service Unavailable');
    echo json_encode([
        'status' => '501',
        'message' => 'Failed to initialize Session'
    ]);
    exit();
}
$loginFailed = true;
$username = array_get_default($_SERVER, 'PHP_AUTH_USER');
$password = array_get_default($_SERVER, 'PHP_AUTH_PW');
$lq = new ListQuery('User');
$lq->addSimpleFilter('username', $username);
$lq->addSimpleFilter('status', 'Active');
$lq->addSimpleFilter('portal_only', '0');
$user = $lq->runQuerySingle();
if (!$user->failed) {
    $pw = $user->getField('cti_hash');
    $enc = new v4_crm_crypt();
    if ($password == $enc->decryptAES($pw, $enc->getSalt())) {
        $loginFailed = false;
    }
}

if ($loginFailed) {
    header('WWW-Authenticate: Basic realm="My Realm"');
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode([
        'status' => '401',
        'message' => 'Unauthorized'
    ]);
    exit;
}

// vorwahlen und auch führende 0 entfernen
$number = preg_replace('/^(\+49|0049|0 49|049|0)/', '', $_REQUEST['q']);
error_log($number);
$lookup = new ctiLookup();
$res = $lookup->queryModulesByPhoneNumber($number);
$firstHit = current($res);

if ($firstHit === false) {
    echo json_encode([
            'records' => []
        ]
    );
}
//error_log(print_r($firstHit, 1));
$result = json_encode([
        'records' => [
            array_merge(
                $firstHit->getRow(true),
                ['URL' => $lookup->getDetailUrl($firstHit)]
            )
        ]

    ]
);

echo $result;

class ctiLookup
{
    protected $modules = array(
        'Contact',
        'Account',
        'Lead',
    );

    public function queryModuleByPhoneNumber($module, $number)
    {
        require_once('include/ListView/ListFormatter.php');
        $fmt = new \ListFormatter($module);
        $lq =& $fmt->getQuery();

        $filter =& $fmt->getFilterForm();

        $filter->loadFilterLayout('Standard');

        $filter_data = array(
            'any_phone' => $number
        );


        $fmt->loadFilter($filter_data);
        //$lq->addField('name');
        //$lq->addField('_display');
        $res = $fmt->getQueryResult(0, 5, 'date_modified DESC');
        if (!$res || $res->failed)
            return [];
        return $res->getRowResults();
    }

    /**
     * @param $number
     * @return RowResult[]
     */
    public function queryModulesByPhoneNumber($number)
    {
        $number = preg_replace('~\D~', '', $number);
        $rows = [];
        foreach ($this->modules as $module) {
            $res = $this->queryModuleByPhoneNumber($module, $number);
            $rows = array_merge($rows, $res);
        }
        return $rows;
    }

    public function getDetailUrl(RowResult $rowResult)
    {
        $baseUrl = AppConfig::setting('site.base_url', '/');
        $module = $rowResult->getModuleDir();
        $id = $rowResult->getPrimaryKeyValue();
        $action = 'DetailView';
        return $baseUrl . '/index.php?module=' . $module . '&action=' . $action . '&record=' . $id;
    }
}