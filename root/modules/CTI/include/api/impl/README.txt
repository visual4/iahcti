This directory contains core files of the UCP.PHP API.

NO CHANGES TO THESE FILES ARE REQUIRED BY THE USER!
These files should not be referenced directly by client implementations.

UcpServerProxy.php
The UCP Server Proxy implementation of the API. This class should not be used directly.
The preferred way is to obtain an implementation using the UcpServerFactory.php

UcpServerEventsAdapter.php
A php file that is used to process the UCP events received through index.php
This file should not be altered by client implementations. The preferred way is to set up
the UcpClientFactory.php to create the correct client implementation to be used.

