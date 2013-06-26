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
if (empty($current_user->id))
    return;
require_once 'cti/classes/iCtiAdapter.php';
$ctiAdapter = AppConfig::setting('cti.adapter') . 'Adapter';

if (!$ctiAdapter) $ctiAdapter='starface';
if (!is_file('cti/classes/' . $ctiAdapter . '.php'))
    exit();

require_once 'cti/classes/' . $ctiAdapter . '.php';

/**
 * @var iCtiAdapter Description
 */
$ctiAdapter::dialNumber($_REQUEST['phoneNr']);

sugar_cleanup();
?>
