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

	require_once(dirname(__FILE__)."/impl/UcpServerProxy.php");

	class UcpServerFactory
	{
		static $instance;
		
		/**
		 * Creates a new UCI Server, please provide the username,
		 * password and callback-url for incoming xml-rpc requests
		 * The host and callback url must be a map with the four components
		 * host, port, path and method with the callback pointing to the file ../core/index.php
		 * 
		 * @return phpUCI instance
		 */		
		static function &createUcpServer($username,$password,$host,$callback)
		{
			if(!isset(UcpServerFactory::$instance))
			{
				UcpServerFactory::$instance = new UcpServerProxy($username,$password,$host,$callback);
			}
			return UcpServerFactory::$instance;
		}
		
	}

?>