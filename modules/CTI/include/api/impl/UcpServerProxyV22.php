<?php

require_once(dirname(__FILE__) . "/UcpServerProxy.php");
require_once(dirname(__FILE__) . "/../UcpV22FunctionKeyRequests.php");

class UcpServerProxyV22 extends UcpServerProxy implements UcpServerCommunicationCall, UcpServerConnection, UcpV22FunctionKeyRequests {

    /**
     *
     * @var xmlrpc_client
     */
    protected $client;
    protected $connection = "ucp.v22.server.connection.";
    protected $functionKeyRequest = "ucp.v22.requests.functionKey.";
    protected $communication = "ucp.v22.server.communication.call.";

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

