<?php

/*********************************************************************************
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
 ********************************************************************************/

define('sugarEntry', true);
define('DISABLE_CLEAN_INCOMING_DATA', 1);
$GLOBALS['sugar_version'] = '7.8.15';
chdir("../");

require_once('include/entryPoint.php');
require_once("modules/CTI/include/api/impl/UcpServerEventsAdapter.php");

function log_error($errno, $errstr, $errfile, $errline)
{
	$errstr = "[".date(DATE_ATOM).']'.$errstr.' in '.$errfile.' line '.$errline."\n";
	error_log($errstr, 3,'logs/cti_error.log');
	return true;
}
function ctilog($string, $file='', $line='' ){
	$debug = AppConfig::setting('cti.debugmodus');
	if (!$debug)return;
	log_error('1',$string, $file, $line);
}
set_error_handler('log_error');
ctilog('--------------------Start Listener Call-----------------------------');

UcpServerEventsAdapter::processEvents();

ctilog('--------------------END Listener Call-------------------------------');


?>
