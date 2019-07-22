<?php
$data = json_decode(file_get_contents('php://input'), true);
//error_log(print_r($data, 1), 3, 'journal.log');
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

// logging in 1CRM

require_once 'modules/CTI/include/StarfaceHelper.class.php';
$helper = new StarfaceHelper();
$userObj = new User();
$userObj->id = $user->getField('id');
$userObj->cti_user_id = $user->getField('cti_user_id');


$id = array_get_default($data, 'id', array_get_default($_REQUEST, 'id', rand(1000, 2000)));
$number = preg_replace('/^(\+49|0049|0 49|049|0)/', '', array_get_default($data, 'phone_number', array_get_default($_REQUEST, 'phone_number', '')));

$callstate = [
    'calledNumber' => '10',
    'callerNumber' => $number,
    'state' => 'HANGUP',
    'id' => $id,
    'timestamp' => date('Y-m-d H:i:s'),
    'callerName' => '',
    'calledName' => ''
];
$helper->insertStarfaceMessage($callstate, $userObj);

echo json_encode($callstate);
