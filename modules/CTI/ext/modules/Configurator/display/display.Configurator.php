<?php return; ?>

fields
	cti_host
		config: cti.host
		vname: LBL_CTI_HOST
		type: varchar
		vdesc: LBL_CTI_HOST_DESC
	cti_https
		config: cti.https
		vname: LBL_CTI_HTTPS
		vdesc: LBL_CTI_HTTPS_DESC
		type: bool
		default: true
	cti_uri
		config: cti.uri
		vname: LBL_CTI_URI
		vdesc: LBL_CTI_URI_DESC
		type: varchar
		default: /xml-rpc
	cti_port
		config: cti.port
		vname: LBL_CTI_PORT
		vdesc: LBL_CTI_PORT_DESC
		type: int
		default: 443
	cti_callback_host
		config: cti.callback_host
		vname: LBL_CTI_CALLBACK_HOST
		vdesc: LBL_CTI_CALLBACK_HOST_DESC
		type: varchar
	cti_callback_uri
		config: cti.callback_uri
		vname: LBL_CTI_CALLBACK_URI
		vdesc: LBL_CTI_CALLBACK_URI_DESC
		type: varchar
		default: /cti/listener.php
	cti_callback_port
		config: cti.callback_port
		vname: LBL_CTI_CALLBACK_PORT
		vdesc: LBL_CTI_CALLBACK_PORT_DESC
		type: int
		default: 443
	cti_callback_https
		config: cti.callback_https
		vname: LBL_CTI_CALLBACK_HTTPS
		vdesc: LBL_CTI_CALLBACK_HTTPS_DESC
		type: bool
		default: true
	
