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

require_once(dirname(__FILE__)."/../../client/UcpClientFactory.php");
require_once(dirname(__FILE__)."/../UcpClientCommunicationCall.php");
require_once(dirname(__FILE__)."/../UcpClientConnection.php");
require_once(dirname(__FILE__)."/../../xmlrpc-2.2/lib/xmlrpc.inc");
require_once(dirname(__FILE__)."/../../xmlrpc-2.2/lib/xmlrpcs.inc");

class UcpServerEventsAdapter
{
	/**
		 * The ReceiveCallState event provides a number of properties about the call the state is
	 	 * changing of. This includes the new state of the call, the id of the call it refers to as a
		 * UUID and a timestamp indicating when the call state change took place. The parameters
		 * callerNumber, callerName, calledNumber and calledName provide information about the parties
		 * involved in the call. If a name or number is not known to STARFACE PBX an empty string is
		 * committed. It is possible that information about the caller or the called person gets
		 * available in the middle of a series of events so that the first call state events don�t
		 * provide any details but later events contain full participant information. The call state
		 * Requested is never signaled by the server. It is assumed after a successful return of the
		 * PlaceCall message.
		 *
		 * @param callProperties
		 *        the different properties of the call as array("string" => object)
         *        The constants id, state, timestamp, callerNumber, callerName, calledNumber, calledName
		 *        are used as keys in the map.
		 */
	static public function receiveCallState($callProperties)
	{
		$debug = AppConfig::setting('cti.debugmodus');
        if ($debug){
			ctilog("###ReceiveCallState###\n" . print_r($callProperties, 1));
		}
		$c = $callProperties->getParam(0);
		if(!$c)
		{	// useless request, thank you
			return;
		}
		$callstate = array(
			"id" => $c->structmem("id")->scalarval(),
			"state" => $c->structmem("state")->scalarval(),
			"timestamp" =>  $c->structmem("timestamp")->scalarVal(),
			"callerNumber" => $c->structmem("callerNumber")->scalarVal(),
			"callerName" => $c->structmem("callerName")->scalarVal(),
			"calledNumber" => $c->structmem("calledNumber")->scalarVal(),
			"calledName" => $c->structmem("calledName")->scalarVal()
		);
		
		$client = UcpServerEventsAdapter::createClient();
		$result = $client->receiveCallState($callstate);
		return UcpServerEventsAdapter::createReturnValue($result);
	}

	static public function reset()
	{
		$client = UcpServerEventsAdapter::createClient();
		$result = $client->reset();		
		return UcpServerEventsAdapter::createReturnValue($result);
	}
	
	static private function createClient()
	{
		return UcpClientFactory::createUcpClient($_GET['de_vertico_starface_user']);
	}
	
	static private function createReturnValue($result)
	{
		if($result)
		{
			return new xmlrpcresp(new xmlrpcval($result, "string"));
		}
		else
		{
			return new xmlrpcresp(new xmlrpcval(true, "string"));
		}
	}
	
	static public function processEvents()
	{
		$server = new xmlrpc_server(array(
			"ucp.v20.client.connection.reset" => array("function" => "UcpServerEventsAdapter::reset"),
			"ucp.v20.client.communication.call.receiveCallState" => array("function" => "UcpServerEventsAdapter::receiveCallState")

		), false);
        $debug = AppConfig::setting('cti.debugmodus');
        if ($debug){
			$server->setDebug(2);
		}
		$server->service();

        if ($debug)ctilog($server->debug_info);

	}
}

?>
