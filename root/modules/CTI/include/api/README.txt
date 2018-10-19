This directory contains the main interfaces and factory classes of the UCP.PHP API.

NO CHANGES TO THESE FILES ARE REQUIRED BY THE USER!

UcpClientCommunicationCall.php and UcpClientConnection.php
Interface to be implemented by the UCP Client. STARFACE makes asynchronous callbacks to the function
defined therein to inform the client about new calls for example.

UcpServerCommunicationCall.php and UcpServerConnection.pnp
Interfaces that are implemented by STARFACE and can be called by the client.

UcpServerFactory.php
Factory that can be used by the client to create a UCP Server Proxy. This proxy implements the
two server interfaces and can be used to communicate with STARFACE.