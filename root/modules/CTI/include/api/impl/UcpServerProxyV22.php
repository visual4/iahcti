<?php

require_once(dirname(__FILE__) . "/UcpServerProxy.php");
require_once(dirname(__FILE__) . "/../UcpFunctionKeyRequests.php");

class UcpServerProxyV22 extends UcpServerProxy implements UcpServerCommunicationCall, UcpServerConnection, UcpFunctionKeyRequests {

    /**
     *
     * @var xmlrpc_client
     */
    protected $client;
    protected $connection = "ucp.v22.server.connection.";
    protected $functionKeyRequest = "ucp.v22.requests.functionKey.";
    protected $communication = "ucp.v22.server.communication.call.";

    /**
	 * Initiates a GET-Request with the given parameters for authentication
	 */
	public function __construct($username,$password,$host,$callback){
		if(!isset($username) || !isset($password) || !isset($host) || !isset($callback)){
			return null;
		}

		/**
		 * will be sent to the server
		 * as login header information
		 */
		$this->user=trim($username);
		$this->password=trim($password);
		$this->host=$host['host'];

		$authstring = md5($this->user . "*" .$this->password);
		$uri = $host['path'] ."?de.vertico.starface.auth=" . $authstring;
		$uri .= "&de.vertico.starface.callback.host=". trim($callback['host']);
		$uri .= "&de.vertico.starface.callback.port=". trim($callback['port']);
		$uri .= "&de.vertico.starface.callback.path=". trim($callback['path']);

		if(!empty($callback['method']))
		{
			$uri .= "&de.vertico.starface.callback.type=". trim($callback['method']);
		}
		if(!empty($host['method']))
		{
			$this->client = new xmlrpc_client($uri, $host['host'], $host['port'],$host['method']);
		}
		else
		{
			$this->client = new xmlrpc_client($uri, $host['host'], $host['port']);
		}
		$this->client->setSSLVerifyPeer(false);
		$this->client->setAcceptedCompression(null);
		$this->client->setRequestCompression(null);
	}

    public function probe() {
        $m = new xmlrpcmsg($this->connection . 'keepAlive');
        $response = $this->client->send($m, 5);
        $val = $response->value();

        $val = (is_object($val)) ? $val->scalarVal() : false;

        return $val;
    }

    public function getCallInfoForKey($functionKeyId) {
        return array();
    }

    public function getContactInfoForKey($functionKeyId) {
        return array();
    }

    public function getFunctionKeys() {
        $m = new xmlrpcmsg($this->functionKeyRequest . 'getFunctionKeys');
        $response = $this->client->send($m, 5);
        $val = $response->value();
        if ($val == 0) {
            return false;
        }
        $functionKeys = $this->scalarToArray($val);

        return $functionKeys;
    }

    public function getImageForKey($functionKeyId) {
        return '';
    }

    protected function scalarToArray($scalar) {

        $result = array();
        $arr = $scalar->scalarVal();
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {

                if (is_object($v) && $v instanceof xmlrpcval) {
                    $result[$k] = $this->scalarToArray($v);
                } else {

                    $result[$k] = $v;
                }
            }
        }
        else
            $result = $arr;

        return $result;
    }

}

