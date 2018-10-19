<?php

/**
 * Created by PhpStorm.
 * User: brafreider
 * Date: 03.11.2016
 * Time: 13:19
 */
class sipgateAdapter implements iCtiAdapter
{
    public static function dialNumber($number)
    {
        global $current_user;
        $GLOBALS['log']->debug("SipGate debug: Starting placeCall.php");
        $GLOBALS['log']->debug("SipGate debug: \$_REQUEST['phoneNr'] = {$number}");
        $GLOBALS['log']->debug("SipGate debug: \$_SESSION['authenticated_user_id'] = {$_SESSION['authenticated_user_id']}");

        $username = AppConfig::setting('cti.sipgate_user');
        $password = AppConfig::setting('cti.sipgate_password');
        $localExtension = AppConfig::setting('cti.sipgate_extension');

        $requestParameter = array(
            'RemoteUri' => sprintf('sip:%s@sipgate.de', $number),
            'LocalUri' => sprintf('sip:%s@sipgate.de', $localExtension),
            'TOS' => 'voice'
        );

        $auth = base64_encode(sprintf('%s:%s', $username, $password));

        $request = xmlrpc_encode_request("samurai.SessionInitiate", $requestParameter);

        $context = stream_context_create(
            array('http' => array(
                'method' => "POST",
                'header' => sprintf("Content-Type: text/xml\r\nAuthorization: Basic %s)", $auth),
                'content' => $request,
            ))
        );
        $response_xml = file_get_contents("https://api.sipgate.net/RPC2", false, $context);
        return xmlrpc_decode($response_xml);

    }


}