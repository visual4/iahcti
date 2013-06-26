<?php
require_once('modules/CTI/include/starAstAPI/starAstAPI.php');

/**
 * Description of asteriskAdapter
 *
 * @author brafreider
 */
class asteriskAdapter implements iCtiAdapter {
    
    public static function dialNumber($number) {
        error_reporting(E_ALL);
        ini_set('display_errors', 'On');
        global $current_user;
        //die Asterisk-Verbindung passiert über einen Benutzer...
        $Username = AppConfig::setting('cti.asterisk_user');
        $Password  = AppConfig::setting('cti.asterisk_password');
        // username und Passwort sollten wir verwenden für extension und caller_id
        
        $ServerIP = AppConfig::setting('cti.asterisk_host');
        $Port = AppConfig::setting('cti.asterisk_port');
        
        $extension = $current_user->cti_user_id;
        
        $con = new AstClientConnection();
        if ($con->Login($Username, $Password, $ServerIP, $Port)){
            //TODO: Extension!!
            $number = str_replace('+', '00', $number);
            $number = preg_replace('/[^0-9]+/', '', $number);
            $res = $con->Dial('sip/10', $number);
            //pr2($res);
            return true;
        }
        
    }

}

?>
