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

//prevents directly accessing this file from a web browser
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');	
//global $mod_strings;
class StarfaceGlobalJS{
	
  function echoJavaScript($event,$arguments){
	// starface hack: include ajax callbacks in every sugar page except ajax requests:
	if(empty($_REQUEST["to_pdf"]) && $_REQUEST["action"]!= 'modulelistmenu' &&
		$_REQUEST['action']!='DynamicAction' && empty($_REQUEST['sugar_body_only']))
	{
		echo '<script type="text/javascript" src="include/javascript/jquery/jquery.pack.js"></script>';
		echo '<link rel="stylesheet" type="text/css" media="all" href="modules/Starface/include/starface.css">';
		
		require_once("modules/Starface/include/StarfaceHelper.class.php");
		$currentUserObj = StarfaceHelper::checkCurrentUser();
		if(!empty($currentUserObj->starface_user_c))
			echo '<script type="text/javascript" src="modules/Starface/include/javascript/functions.js"></script>';
		//echo '<script type="text/javascript">var starface_no_calls_lbl = "'.$mod_strings['NO_CALLS'].'";</script>';
	}
  }
}
?>
