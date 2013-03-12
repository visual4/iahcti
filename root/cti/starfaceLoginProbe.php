<?php

/* * *******************************************************************************
 * 
 * STARFACE SugarCRM Connector is a computer telephony integration module for the
 * SugarCRM customer relationship managment program by SugarCRM, Inc.
 *
 * Copyright (C) 2010 STARFACE GmbH
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact STARFACE GmbH at Stephanienstr. 102, 76133 Karlsruhe,
 * GERMANY or at the e-mail address info@starface-pbx.com
 * 
 * ****************************************************************************** */

if (!defined('sugarEntry'))
    define('sugarEntry', true);

chdir("../");

require_once('include/entryPoint.php');
require_once('modules/Contacts/Contact.php');

require_once("modules/CTI/include/api/UcpServerConnection.php");
require_once("modules/CTI/include/api/UcpServerFactory.php");
require_once("modules/CTI/include/StarfaceHelper.class.php");

//error_reporting(E_ALL);
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
    return; // not logged in

$host = StarfaceHelper::getHostArray();

$callback = StarfaceHelper::getCallbackArray();
$starface_user = $current_user->cti_user_id;
$starface_password = $current_user->cti_password;

if (!$starface_user || !$starface_password) exit('not configured');

$server = UcpServerFactory::createUcpServer($starface_user, $starface_password, $host, $callback);

// check if callback configuration has changed
if (serialize($callback) != $_SESSION['cti_callback_array']){
    $_SESSION['cti_callback_array'] = serialize($callback);
    $server->logout();
}

$server->setDebugLevel(0);

$probeReturn = $server->probe();

if ($probeReturn != 1 or true) {
    StarfaceHelper::clearStarfaceLogFor($starface_user, $current_user->db);
    $loginReturn = $server->login();

    $setStatesReturn = $server->setProvidedServices(
            array(
                "ucp.v20.client.communication.call",
                "ucp.v20.client.connection"));
    
    echo "OK, neues Login";
}
else
    print "OK";
?>
