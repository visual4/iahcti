<?php

/**
 * Created by PhpStorm.
 * User: Anton_Haas
 * Date: 15.07.2016
 * Time: 13:27
 */
class SipgateHelper
{
    public static function log($str, $file = "log.log", $append = true){
        error_log($str, 3, $file);
    }

    public function insertSipgateMessage($call, & $user){
        $minDigits = AppConfig::setting('cti.internal_digits', 0);
        if(!$user->id || !$user->cti_user_id || strlen($call['callerNumber'] <= $minDigits )) return false;
        $rowUpdate = RowUpdate::blank_for_model('ctiCall');
        $rowUpdate->set(array(
            'cti_user' => $user->cti_user_id,
            'cti_id' => $call['id'],
            'state' => $call['state'],
            'timestamp' => $call['timestamp'],
            'caller_number' => $call['callerNumber'],
            'caller_name' => utf8_encode($call['callerName']),
            'called_number' => $call['calledNumber'],
            'called_name' => utf8_encode( $call['calledName']),
        ));

        if (!$rowUpdate->validate()){
            SipgateHelper::log(print_r($rowUpdate->errors, 1));
            return false;
        }

        $rowUpdate->save();

        return true;
    }

    public function retrieveBySipgateUser($sipgateUsername)
    {
        require_once('modules/Users/User.php');
        $user = new User();

        $query = 'SELECT  id
					FROM users 
					WHERE user_name = \'' . $sipgateUsername . '\' 
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