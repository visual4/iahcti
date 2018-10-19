This directory contains the files to be adpated by client implementations that want to receive
UCI events from STARFACE.

UcpClientFactory.php
Factory used to create the UCP Client. This must be adapted by the client implementor to
construct the client implementation of the application instead of the UcpDemoClient.

DefaultUcpClient.php
The default UCP Client implementation that is used if no other implementation has been set
to be used in the UcpClientFactory.php Create your own implementation of the interfaces
UcpClientCommunicationCall, UcpClientConnection to receive events from STARFACE

index.php
The callback PHP file called by STARFACE to send UCP events to the client implementation.
This file can be altered and placed elswhere when required by the client implementation.
When doing so make sure to correct the included file's paths.