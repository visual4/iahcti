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
 	 * 
 	 * This interface defines the connection procedures provided by the UCP server.
 	 * 
 	 * @author stefan ernst
 	 * 
 	 */
	interface UcpServerConnection
	{

		/**
		 * The connectionLogin message is sent by the client to connect to the UCP server instance.
		 * 
		 * @return The return value of the message indicates if the login was successful.
		 */
		public function login();
		
		/**
		 * The client uses the connectionLogout procedure call without any request parameters to
		 * indicate its intention to close the UCP connection. The response of the server is always
		 * <code>true</code> and may therefore be ignored.
		 * 
		 * @return allways <code>true</code>
		 */
		public function logout();
		
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
		public function probe();
		
		/**
		 * The client logged in at the server may provide a list of service names depending on the
		 * messages it can receive. The server will then only send the messages advertised in this way.
		 * The client is not required to provide any services. In this case an empty list is provided.
		 * In order to be able to receive asynchronous events from the server services should be
		 * provided. This method to get informed of server state is preferred over polling.
		 * 
		 * @param serviceNames
		 *        a list of String denoting the services the client provides
		 * @return allways <code>true</code>
		 * @throws UcpException
		 *         if the procedure call is not valid as defined by the UCP specification. This may be
		 *         the case when the call is placed though the UCP client is not connected (<code>{@link UcpNotConnectedException}</code>).
		 */
		public function setProvidedServices($serviceNames);	
	}

?>