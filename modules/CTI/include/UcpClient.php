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

/**
 * currently empty values are returned, it may be possible to replace those
 * with actual information in case its needed
 */
	 
require_once(dirname(__FILE__)."/StarfaceHelper.class.php");

class UcpClient
{
	private $starface_user_id;

	public function __construct($userid)
	{
		$this->starface_user_id = $userid;
	}

	public function receiveCallState($callstate)
	{
		if($callstate['state'] == 'INCOMING')
		{
			// we do not need this event
			return;
		}

		$starface = new StarfaceHelper();
		$user_id = $starface->retrieveByStarfaceUser($this->starface_user_id);
		$GLOBALS['log']->debug("Starface debug: starface_user_id = {$this->starface_user_id}");
		$GLOBALS['log']->debug("Starface debug: \$user_id = $user_id");
		if(empty($user_id))
		{
			return new xmlrpcresp(
				new xmlrpcval(
					array(
						'errorCode' => '0001', 
						'errorMessage' => 'No SugarUser found with given StarfaceUsername: ' . $this->starface_user_id), 
					"string"));
				
		}
		else {
			$currentUser = new User();
			$currentUser->retrieve($user_id);
		}

		
		$GLOBALS['log']->debug("Starface debug: \$callstate = $callstate");
		$GLOBALS['log']->debug("Starface debug: \$currentUser->user_name = {$currentUser->user_name}");
		if(!$starface->insertStarfaceMessage($callstate, $currentUser))
		{
			trigger_error("Insert failed! " . print_r($callstate,true));
			return new xmlrpcresp(
				new xmlrpcval(
					array(
						'errorCode' => '0002', 
						'errorMessage' => 'insertStarfaceMessage() FAILED with callProperties Object: ' . print_r($callstate,true)), 
					"string"));
		}
		
		return;
	}
	
	public function reset()
	{
		// nothings happening - amazing!
		return;
	}
	
}

?>
