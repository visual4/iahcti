<?php
error_reporting(E_ALL);
ini_set("display_errors","On");
/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 06.08.15
 * Time: 15:37
 */
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

//require_once __DIR__ . '/wooWebHook.php';
//require_once __DIR__ . '/classes/generic/staticLogger.php';
require_once 'include/UcpClient.php';
require_once 'include/SipgateHelper.class.php';
require_once 'include/utils/html_utils.php';

global $json_supported_actions;
$json_supported_actions['starface_wh_request'] = array(
    'login_required' => false,
    'class_name' => 'ctiWebHooks',
    'function_name' => 'starface_wh_request',
);
$json_supported_actions['sipgate_wh_request'] = array(
    'login_required' => false,
    'class_name' => 'ctiWebHooks',
    'function_name' => 'sipgate_wh_request',
);

// TEST REQUESTS
// http://crm.local/json.php?module=CTI&action=starface_wh_request
// http://crm.local/json.php?module=CTI&action=sipgate_wh_request
// http://crm.local/cti/checkForNewStates.php?myTimestamp=2016-06-29+18:04:27

class ctiWebHooks
{
    function starface_wh_request()
    {
        define('CTI_WEBHOOK', true);

        $headers = getallheaders();
        $headers = array_change_key_case($headers, CASE_LOWER);

        /*-testdata-*/
        $_POST['cti_id'] = md5(time()); // immer neue id
        $headers['cti-webhook-host'] = 'telefon.visual4.de';
        $_POST['user_id'] = 26;
        $_POST['timestamp'] = date('Y-m-d') . 'T' . date('H:i:s');
        $_POST['state'] = 'RINGING';
        $_POST['callerNumber'] = '123456789';
        $_POST['callerName'] = '';
        $_POST['calledNumber'] = '987654321';
        $_POST['calledName'] = 'ich';
        /*-/testdata-*/

        $cti_host = $headers['cti-webhook-host'] = 'telefon.visual4.de';
        if ($cti_host != AppConfig::setting('cti.host'))
            json_bad_request('wrong signature or not configured in 1CRM');

        $callstate = array(
            'id' => $_POST['cti_id'], // immer neue id
            'state' => $_POST['state'],
            'timestamp' => $_POST['timestamp'],
            'callerNumber' => $_POST['callerNumber'],
            'callerName' => utf8_encode($_POST['callerName']),
            'calledNumber' => $_POST['calledNumber'],
            'calledName' => utf8_encode($_POST['calledName'])
        );

        $starface = new StarfaceHelper();
        $user_id = $starface->retrieveByStarfaceUser($_POST['user_id']);
        $currentUser = new User();
        $currentUser->retrieve($user_id);

        $GLOBALS['log']->debug("Starface debug: \$callstate = $callstate");
        $GLOBALS['log']->debug("Starface debug: \$currentUser->user_name = {$currentUser->user_name}");

        if ($starface->insertStarfaceMessage($callstate, $currentUser))
            $return = 'success';
        else
            $return = 'error';

        json_return_value($return);
        die();
    }

    function sipgate_wh_request()
    {
        define('CTI_WEBHOOK', true);

        $headers = getallheaders();
        $headers = array_change_key_case($headers, CASE_LOWER);

        /*-testdata-*/
        $_POST['cti_id'] = md5(time()); // immer neue id
        $headers['cti-webhook-host'] = 'telefon.visual4.de';
        $_POST['timestamp'] = date('Y-m-d') . 'T' . date('H:i:s');
        //new call
        $_POST['event'] = 'newCall';
        $_POST['from'] = '492111234567';
        $_POST['to'] = '4915791234567';
        $_POST['direction'] = 'in';
        $_POST['callId'] = '123456';
        $_POST['user'][] = 'Alice';
        $_POST['user'][] = 'Bob';
        //answer
//        $_POST['event'] = 'answer';
//        $_POST['callId'] = '123456';
//        $_POST['user'] = 'John+Doe';
//        $_POST['from'] = '492111234567';
//        $_POST['to'] = '4915791234567';
//        $_POST['direction'] = 'in';
//        $_POST['answeringNumber'] = '21199999999';
        //hangup
//        $_POST['event'] = 'hangup';
//        $_POST['cause'] = 'normalClearing';
//        $_POST['callId'] = '123456';
//        $_POST['from'] = '492111234567';
//        $_POST['to'] = '4915791234567';
//        $_POST['direction'] = 'in';
//        $_POST['answeringNumber'] = '4921199999999';
        /*-/testdata-*/

        $cti_host = $headers['cti-webhook-host'];
        if ($cti_host != AppConfig::setting('cti.host'))
            json_bad_request('wrong signature or not configured in 1CRM');

        if($_POST['direction'] == 'out')
            die();

        $event = $_POST['event'];           // "newCall" (beginning), "answer" or "hangup" (end)
        $call['id'] = $_POST['callId'];         // unique Id of this call
        $call['timestamp'] = date('Y-m-d') . 'T' . date('H:i:s');   // a timestamp for the log so that calls can be identified
        $call['callerNumber'] = $_POST['from'];
        $call['callerName'] = '';
        $call['calledNumber'] = $_POST['to'];
        $call['calledName'] = is_array($_POST['user']) ? $_POST['user'][0] : $_POST['user'];

        if ($event == 'newCall') {
            $call['state'] = 'RINGING';
        } else if ($event == 'answer') {
            $call['state'] = 'CANCELLED'; //TODO which state?
        } else if ($event == 'hangup') {
            $call['state'] = 'CANCELLED';
        }

        $sipgate = new SipgateHelper();

        $user_id = $sipgate->retrieveBySipgateUser($call['calledName']);
        $currentUser = new User();
        $currentUser->retrieve($user_id);

        $GLOBALS['log']->debug("Sipgate debug: \$call = $call");
        $GLOBALS['log']->debug("Sipgate debug: \$currentUser->user_name = {$currentUser->user_name}");

        if ($sipgate->insertSipgateMessage($call, $currentUser))
            $return = 'success';
        else
            $return = 'error';

        json_return_value($return);
        die();
    }

//    public static function checkRequest($signature, $data)
//    {
//        $secret = AppConfig::setting('woocommerce.webhook_secret');
//        if (empty($secret)) return false;
//        $hash_raw = hash_hmac('sha256', $data, $secret, true);
//        $hash = base64_encode($hash_raw);
//        if ($hash == $signature) {
//            global $current_user, $authController;
//            $current_user = new User();
//            $current_user->retrieve(\AppConfig::setting('woocommerce.assigned_user', 1));
//            $authController->sessionAuthenticate(\AppConfig::setting('woocommerce.assigned_user', 1));
//            $current_user->authenticated = true;
//            return true;
//        }
//        return false;
//
//    }

}

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = '';
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}