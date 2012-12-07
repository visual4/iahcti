<?php

/*********************************************************************************
 * 
 * UCP.PHP (STARFACE User Call Protocol PHP API) is a library for communication
 * with STARFACE PBX.
 *
 * Copyright (C) 2008 vertico software GmbH
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
 * You can contact vertico software GmbH at Amalienstr. 81-87, 76133 Karlsruhe,
 * GERMANY or at the e-mail address info@vertico-software.com
 * 
 ********************************************************************************/

require_once(dirname(__FILE__)."/../UcpServerCommunicationCall.php");
require_once(dirname(__FILE__)."/../UcpServerConnection.php");
require_once(dirname(__FILE__)."/../../xmlrpc-2.2/lib/xmlrpc.inc");
require_once(dirname(__FILE__)."/../../xmlrpc-2.2/lib/xmlrpcs.inc");
 
/**
 * This is the UCP server Proxy implementation used by clients to communicate with STARFCE
 * It should not be instantiated by clients. The preferred method is to obtain an instance
 * using the UcpServerFactory
 *
 * IMPORTANT: trigger_error doesnt trigger an actual error but
 * 			  an E_USER_NOTICE
 * 
 * @author stefan ernst
 */
class UcpServerProxy implements UcpServerCommunicationCall, UcpServerConnection
{

	private $client;

	private $user;
	private $password;
	private $host;

	private $debuglevel;

	private $connection = "ucp.v20.server.connection.";
	private $communication = "ucp.v20.server.communication.call.";

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

	public function getDebugLevel()
	{
		return $this->debuglevel;
	}

	public function setDebugLevel($l)
	{
		$this->debuglevel = $l;
		$this->client->setDebug($l);			
	}

	/**
	 * Returns a numeric list with phone-ids
	 *
	 * @return array("string");
	 */
	public function getPhoneIds()
	{
		return $this->requestNoParamArrayResp($this->communication.'getPhoneIds');
	}
	
	private function requestNoParamArrayResp($functionname)
	{
		$m=new xmlrpcmsg($functionname);
		$response = $this->client->send($m);
		
		$val = $response->value();
		
		if($val == 0)
		{
			return false;
		}
		
		if($val->kindOf() != "array")
		{
			trigger_error("Unknown response received:" . $val);
		}
		
		$r = array();
		
		for($i=0;$i<$val->arraySize();$i++)
		{
			$r[] = $val->arrayMem($i)->scalarVal();
		}
		
		return $r;
	}
	
	/**
	 * Returns a numeric list with call-ids
	 *
	 * @return array("string");
	 */
	public function getCallIds()
	{
		return $this->requestNoParamArrayResp($this->communication.'getCallIds');
	}

	/**
	 * Returns the state for a give call-id.
	 * String properties in the list are:
	 * id, state, timestamp, callerNumber, callerName, calledNumber, calledName
	 *
	 * @param callId - determines the specific call you need the state for
	 * @return array("string" => object);
	 */
	public function getCallState($callid)
	{
		if(!is_string($callid))
		{
			trigger_error($callid . " is not a string, invalid parameter.");
		}
		
		$m=new xmlrpcmsg($this->communication.'getCallState');
		$m->addParam(new xmlrpcval($callid,"string"));
		$response = $this->client->send($m);
		
		$val = $response->value();
		
		if($val == 0)
		{
			return false;
		}
		
		$callstate = array();
		
		while (list($key, $v) = $val->structEach())
		{
		  $callstate[$key] = $v->scalarVal();
		}
		
		return $callstate;
	}

	/**
	 * Provide a phone number to place a call to. PhoneId and CallId
	 * are optional.
	 * 
	 * @param phoneNumber - the telephone number to call
	 * @param phoneId - the telephone to use (optional, use "" instead)
	 * @param callId - if you wish to use a specific call-id for this call (optional, use "" instead)
	 * 
	 * @return the callId
	 */
	public function placeCall($phoneNumber,$phoneId = "",$callId = "")
	{
		if(!is_string($callId) || !is_string($phoneId) || !is_string($phoneNumber))
		{
			trigger_error($callId . " / $phoneId / $phoneNumber is not a string, invalid parameter.");
		}
		
		$m=new xmlrpcmsg($this->communication.'placeCall');
		$m->addParam(new xmlrpcval($phoneNumber,"string"));
		$m->addParam(new xmlrpcval($phoneId,"string"));
		$m->addParam(new xmlrpcval($callId,"string"));
		
		$response = $this->client->send($m);
		$val = $response->value();
		
		$val = (is_object($val)) ? $val->scalarVal() : false;
		
		return $val;
	}

	/**
	 * Terminates the call
	 * 
	 * @param callId
	 * @return boolean
	 */
	public function hangupCall($callId)
	{
		$m=new xmlrpcmsg($this->communication.'hangupCall');
		$m->addParam(new xmlrpcval($callId,"string"));
		
		$response = $this->client->send($m);
		$val = $response->value();
		
		$val = ($val != 0) ? $val->scalarVal() : false;
			
		return $val;
	}


	/**
	 * The connectionLogin message is sent by the client to connect to the UCP server instance. The
	 * client logging in at the server must provide a list of service names depending on the
	 * messages it can receive. The server will then only try to send the messages advertised in
	 * this way. The client is not required to provide any services. In this case an empty list is
	 * provided. In order to be able to receive asynchronous events from the server services should
	 * be provided. This method to get informed of server state is preferred over polling.
	 * 
	 * @param services
	 *        A list of Strings containing the method's names the UCP client provides
	 * @return The return value of the message indicates if the login was successful.
	 */
	public function login()
	{
		$m=new xmlrpcmsg($this->connection.'login');
		$response = $this->client->send($m);
		$val = $response->value();
		
		$val = (is_object($val)) ? $val->scalarVal() : false;

		return $val;
	}

	/**
	 * The client uses the connectionLogout procedure call without any request parameters to
	 * indicate its intention to close the UCP connection. The response of the server is always
	 * <code>true</code> and may therefore be ignored.
	 * 
	 * @return always <code>true</code>
	 */
	public function logout()
	{
		$m=new xmlrpcmsg($this->connection.'logout');
		$response = $this->client->send($m);
		$val = $response->value();
			
		return true;
	}
	
	/**
	 * The connectionProbe message is used to check if the server is still available, the connection
	 * has not been lost and tell the server that the client is still available.
	 * 
	 * @return A return value of <code>true</code> denotes that the client connection to the
	 *         server is still available and <code>false</code> tells the client that it is not
	 *         logged in from the servers point of view. In the case of the <code>false</code>
	 *         return value the client must discard all information about calls it had previously
	 *         received from the server and probably login again.
	 */
	public function probe()
	{
		$m=new xmlrpcmsg($this->connection.'probe');
		$response = $this->client->send($m,5);
		$val = $response->value();
		
		$val = (is_object($val)) ? $val->scalarVal() : false;
			
		return $val;
	}

	/**
	 * The client logged in at the server may provide a list of service names depending on the
	 * messages it can receive. The server will then only send the messages advertised in this way.
	 * The client is not required to provide any services. In this case an empty list is provided.
	 * In order to be able to receive asynchronous events from the server services should be
	 * provided. This method to get informed of server state is preferred over polling.
	 * 
	 * @param serviceNames
	 *        a list of String denoting the services the client provides
	 * @return <code>true</code> if successful, <code>false</code> if otherwise
	 * @throws UcpException
	 *         if the procedure call is not valid as defined by the UCP specification. This may be
	 *         the case when the call is placed though the UCP client is not connected (<code>{@link UcpNotConnectedException}</code>).
	 */
	public function setProvidedServices($serviceNames)
	{
		$params = array();
		
		foreach($serviceNames as $v)
		{
			$params[] = new xmlrpcval($v,"string");
		}			

		$m=new xmlrpcmsg($this->connection.'setProvidedServices');
		$m->addParam(new xmlrpcval($params,"array"));
		$response = $this->client->send($m);

		$val = $response->value();
		
		$val = (is_object($val)) ? $val->scalarVal() : false;

		return $val;
	}

}

?>