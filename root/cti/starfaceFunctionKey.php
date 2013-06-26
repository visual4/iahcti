<?php


define('sugarEntry', true);

chdir('../');
require_once('include/entryPoint.php');

AppConfig::session_start();

require_once("modules/CTI/include/api/UcpServerFactory.php");
require_once("modules/CTI/include/StarfaceHelper.class.php");

$authController = new AuthenticationController();
if (isset($_SESSION['authenticated_user_id'])) {
    if (!$authController->sessionAuthenticate()) {
        session_destroy();
        indexRedirect(array('module' => 'Users', 'action' => 'Login'));
    }
    $GLOBALS['log']->debug('Current user is: ' . $current_user->user_name);
    $current_user->update_access_time();
}

if (empty($current_user->id)){
    echo 'not logged in';
    return; // not logged in
}

$host = StarfaceHelper::getHostArray();

$callback = StarfaceHelper::getCallbackArray();
$starface_user = $current_user->cti_user_id;
$starface_password = $current_user->cti_password;

if (!$starface_user || !$starface_password) exit('not configured');

$server = UcpServerFactory::createUcpServer($starface_user, $starface_password, $host, $callback, "V22");

$server->setDebugLevel(0);

$response = $server->getFunctionKeys();

echo "<pre>".htmlspecialchars(print_r($response,1))."</pre>";
echo "ende";