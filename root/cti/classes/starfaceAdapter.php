<?php

require_once("modules/CTI/include/api/UcpServerCommunicationCall.php");
require_once("modules/CTI/include/api/UcpServerConnection.php");
require_once("modules/CTI/include/api/UcpServerFactory.php");
require_once("modules/CTI/include/StarfaceHelper.class.php");
require_once 'modules/CTI/v4_crm_crypt.php';

/**
 * Description of starfaceAdapter
 *
 * @author brafreider
 */
class starfaceAdapter implements iCtiAdapter
{

    public static function dialNumber($number)
    {
        global $current_user;
        $GLOBALS['log']->debug("Starface debug: Starting placeCall.php");
        $GLOBALS['log']->debug("Starface debug: \$_REQUEST['phoneNr'] = {$number}");
        $GLOBALS['log']->debug("Starface debug: \$_SESSION['authenticated_user_id'] = {$_SESSION['authenticated_user_id']}");

        $host = StarfaceHelper::getHostArray();
        $callback = StarfaceHelper::getCallbackArray();
        $cti_user = $current_user->cti_user_id;
        $cti_password = $current_user->cti_password;
        if ($cti_password == 'XXXXXX') {
            $enc = new v4_crm_crypt();
            $cti_password = $enc->decryptAES($current_user->cti_hash, $enc->getSalt());
        }

        $server = UcpServerFactory::createUcpServer($cti_user, $cti_password, $host, $callback);
        $server->setDebugLevel(0);

        if ($server->probe() != 1) {
            $loginReturn = $server->login();
        }


        $setStatesReturn = $server->setProvidedServices(
            array(
                "ucp.v20.client.communication.call",
                "ucp.v20.client.connection"));

        $callid = $server->placeCall($number);

        return $callid;
    }

}

?>
