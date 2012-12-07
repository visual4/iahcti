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

	// please
    // providedServices[] = "ucp.v20.client.communication.call";
	// when implenting this interface

	/**
	 * This interface defines communication procedures that have to be provided by a UCP client
	 * implementation in order to handle phone calls.
     *
	 * @author stefan ernst
	 */
	interface UcpClientCommunicationCall
	{
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
		public function receiveCallState($callProperties);
		
	}

?>