<?php
	
	require_once(dirname(__FILE__)."/../api/UcpClientCommunicationCall.php");
	require_once(dirname(__FILE__)."/../api/UcpClientConnection.php");
	
	/**
	 * This is the default UCP client implementation used if no other has been set in the
	 * UcpClientFactory.
	 */
	class DefaultUcpClient implements UcpClientCommunicationCall, UcpClientConnection
	{
		
		public function __construct($userid)
		{
			// Nothing to do here
		}
		
		/**
		 * The ReceiveCallState event provides a number of properties about the call the state is
	 	 * changing of. This includes the new state of the call, the id of the call it refers to as a
		 * UUID and a timestamp indicating when the call state change took place. The parameters
		 * callerNumber, callerName, calledNumber and calledName provide information about the parties
		 * involved in the call. If a name or number is not known to STARFACE PBX an empty string is
		 * committed. It is possible that information about the caller or the called person gets
		 * available in the middle of a series of events so that the first call state events don’t
		 * provide any details but later events contain full participant information. The call state
		 * Requested is never signaled by the server. It is assumed after a successful return of the
		 * PlaceCall message.
		 * 
		 * @param callProperties
		 *        the different properties of the call as array("string" => object)
         *        The constants id, state, timestamp, callerNumber, callerName, calledNumber, calledName
		 *        are used as keys in the map.
		 */
		public function receiveCallState($callProperties)
		{
			trigger_error("DefaultUcpClient does not implement receiveCallState!");	
		}
		
		/**
		 * STARFACE wants to reset the UCP connection and doesn't send any further calls to the client.
		 */
		public function reset()
		{
			trigger_error("DefaultUcpClient does not implement reset!");
		}
	}
?>