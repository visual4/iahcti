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

class StarfaceHelper{
	public static function checkRequestParams(array $params, $method = "GET"){
		// TODO : differ between GET and POST
		foreach($params as $param){
			if(!isset($_REQUEST[$param])) {
				session_destroy();
				header("Location: ". $GLOBALS['sugar_config']['site_url']);
				exit();
			}

		}
	}


	public static function checkCurrentUser(){
		// check current user, redirect to homepage when not logged in
		if(!isset($_SESSION['authenticated_user_id'])) {
			session_destroy();
			//print json_encode(array(".")); //###insignio### don't show ["."] on logout screen
			exit(0);
		}
		$current_user = new User();
		$result = $current_user->retrieve($_SESSION['authenticated_user_id']);
		if($result == null) {
			session_destroy();
			//print json_encode(array(".")); //###insignio###
			exit(0);
		}
		return $result;

	}

	public static function getHostArray(){
		$host = array(
			"host" => AppConfig::setting('cti.host'),
			"path" => AppConfig::setting('cti.uri'),
			"port" => AppConfig::setting('cti.port'),
			"method" => ((AppConfig::setting('cti.https')) ? true : false),
			
		);
		return $host;
	}

	public static function getCallbackArray($result=false){
		$callback = array(
			"host" => 	AppConfig::setting('cti.callback_host'),
			"path" => 	AppConfig::setting('cti.callback_uri'),
			"port" => 	AppConfig::setting('cti.callback_port'),
			"method" => AppConfig::setting('cti.callback_https')? true : false,
			
		);
		return $callback;
	}

	//not used... (ralf.eckhardt@insignio.de)
	public static function clearStarfaceLog($sfUserID, $term, $count, $dbObj){

		// clean up database table starface_log
		$cleanQuery = "
			DELETE FROM starface_log 
			where ";
		
		$cleanQuery .=	" TIMESTAMPDIFF($term,mysql_time,CURRENT_TIMESTAMP) > $count AND ";
		$cleanQuery .=	" starface_user = '$sfUserID' ";

		$innerResultSet = $dbObj->query($cleanQuery, false);
		if($dbObj->checkError()){
			StarfaceHelper::log("clearStarfaceLog :: query failed: " . $cleanQuery, "StarfaceHelper.log");
		}

	}
	
public static function clearStarfaceLogFor($sfUserID, $dbObj){

		// clean up database table starface_log
		$cleanQuery = "
			DELETE FROM cti_call 
			where ";
		
		$cleanQuery .=	" cti_user = '$sfUserID' ";

		$innerResultSet = $dbObj->query($cleanQuery, false);
		if($dbObj->checkError()){
			StarfaceHelper::log("clearStarfaceLogFor :: query failed: " . $cleanQuery, "StarfaceHelper.log");
		}

	}

	public static function log($str, $file = "log.log", $append = true){
		error_log($str, 3, $file);
	}

	
	
	public function insertStarfaceMessage($callstate, & $user){
		if(!$user->id || !$user->cti_user_id) return false;
		$rowUpdate = RowUpdate::blank_for_model('ctiCall');
		$rowUpdate->set(array(
			'cti_user' => $user->cti_user_id,
			'cti_id' =>$callstate['id'],
			'state' =>$callstate['state'],
			'timestamp' =>$callstate['timestamp'],
			'caller_number' =>$callstate['callerNumber'],
			'caller_name' =>$callstate['callerName'],
			'called_number' =>$callstate['calledNumber'],
			'called_name' =>$callstate['calledName'],
		));
		
		if (!$rowUpdate->validate()){
			StarfaceHelper::log(print_r($rowUpdate->errors, 1));
			return false;
		}
		
		$rowUpdate->save();
		
		return true;
	}

	private function checkStarfaceTable(){
		// <se@starface.de>: ??
	}

	public function retrieveByStarfaceUser($starfaceUsername)
	{
		require_once('modules/Users/User.php');
		$user = new User();
		
			$query = 'SELECT  id
					FROM users 
					WHERE cti_user_id = \'' . $starfaceUsername . '\' 
					limit 1';
		
		$r = $user->db->query($query, false);

		if($a = $user->db->fetchByAssoc($r)){
			return $a['id'];
		}
		else {
			return null;
		}
		
	}

}
?>
