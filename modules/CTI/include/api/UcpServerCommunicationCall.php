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

	/**
	 * This interface defines the call communication procedures provided by the UCP server.
	 * 
	 * @author stefan ernst
	 */
	interface UcpServerCommunicationCall
	{ 
		/**
		 * The UCP client may call getPhoneIds to have the server send a response with a list of
		 * telephone ids of the phones assigned to the user.
		 * 
		 * @return array("string") representing the ids of the users telephones
		 * @throws UcpException
		 *         if the procedure call is not valid as defined by the UCP specification. This may be
		 *         the case when the call is placed though the UCP client is not connected (<code>{@link UcpNotConnectedException}</code>).
		 */
		public function getPhoneIds();

		/**
		 * The UCP client may call getCallIds to have the server send a response with a list of call ids
		 * of the call being held by the user.
		 * 
		 * @return array("string") with the UUIDs assigned to the existing phone calls
		 * @throws UcpException
		 *         if the procedure call is not valid as defined by the UCP specification. This may be
		 *         the case when the call is placed though the UCP client is not connected (<code>{@link UcpNotConnectedException}</code>).
	 	 */
		public function getCallIds();

		/**
		 * The getCallState call provides a number of properties about an identified call. This includes
		 * the state of the call. The parameters callerNumber, callerName, calledNumber and calledName
		 * provide information about the parties involved in the call. If a name or number is not known
		 * to the UCP server an empty string is committed. It is possible that information about the
		 * caller or the called person is available on a later call.
		 * 
		 * @param callId
		 *        the UUID assigned to the phone call
		 * @return the different properties of the call as array("string" => object)
             *         The constants id, state, timestamp, callerNumber, callerName, calledNumber, calledName
             *         are used as keys in the map. If the call is not known an empty map is returned.
		 * @throws UcpException
		 *         if the procedure call is not valid as defined by the UCP specification. This may be
		 *         the case when the call is placed though the UCP client is not connected (<code>{@link UcpNotConnectedException}</code>).
		 */
		public function getCallState($callId);	

		/**
		 * This procedure is called to initiate a phone call for the user the same way as when using the
		 * STARFACE call manager. The phone number to call and phone to use can be selected using the
		 * request parameters. In UCP phone calls are identified using UUIDs (Universally Unique
		 * Identifier). The id for the call to be placed can either be given by the client or generated
		 * by STARFACE PBX. The server may reject the request to place the call due to invalid
		 * parameters given or due to another place call request being in progress. If the result of the
		 * message is a valid UUID the call request is accepted and the call is in the state Requested.
		 * 
		 * @param phoneNumber
		 *        The phone number to dial
		 * @param phoneId
		 *        The id of the telephone used to make the phone call. If not set the primary phone will
		 *        be used.
		 * @param callId
		 *        The UUID to be assigned to the phone call. If not set STARFACE PBX will create one.
		 * @return an empty string if the place call request was rejected by the server or the UUID
		 *         assigned to the call otherwise
		 * @throws UcpException
		 *         if the procedure call is not valid as defined by the UCP specification. This may be
		 *         the case when the call is placed though the UCP client is not connected (<code>{@link UcpNotConnectedException}</code>).
		 */
		public function placeCall($phoneNumber,$phoneId = "",$callId = "");

		/**
		 * This message tells UCP server to hang up the call identified by the given UUID.
		 * 
		 * @param callId
		 *        the UUID of the call to be hung up
		 * @return <code>true</code> if the attempt to hang up the call was successful,
		 *         <code>false</code> otherwise
		 * @throws UcpException
		 *         if the procedure call is not valid as defined by the UCP specification. This may be
		 *         the case when the call is placed though the UCP client is not connected (<code>{@link UcpNotConnectedException}</code>).
		 */
		public function hangupCall($callId);
		
	}


?>
