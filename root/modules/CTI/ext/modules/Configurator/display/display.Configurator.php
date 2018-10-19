<?php return; ?>

fields
    cti_adapter
        config: cti.adapter
        vname: LBL_CTI_ADAPTER
        type: enum
        options: cti_adapter_dom        
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
	cti_asterisk_user
        config: cti.asterisk_user
        vname: LBL_CTI_ASTERISK_USER 
        vdesc: LBL_CTI_ASTERISK_USER_DESC
        type: varchar
	cti_asterisk_password
        config: cti.asterisk_password
        vname: LBL_CTI_ASTERISK_PASSWORD 
        vdesc: LBL_CTI_ASTERISK_PASSWORD_DESC
        type: password
    cti_asterisk_host
		config: cti.asterisk_host
		vname: LBL_CTI_HOST
		type: varchar
		vdesc: LBL_CTI_HOST_DESC
    cti_asterisk_port
		config: cti.asterisk_port
		vname: LBL_CTI_PORT
		vdesc: LBL_CTI_PORT_DESC
		type: varchar
		default: 5038
    cti_internal_digits
        config: cti.internal_digits
        vname: LBL_INTERNAL_DIGITS
        vdesc: LBL_INTERNAL_DIGITS_DESC
        type: number
        default: 2
    cti_starface_auth_type
        config: cti.starface_auth_type
        type: enum
        vname: LBL_STARFACE_AUTH_TYPE
        vdesc: LBL_STARFACE_AUTH_TYPE_DESC
        options
            sha512: Starface Auhentication (SHA512)
            #activeDirectory: Active Directory Auth (Base64)
            md5: legacy Authentication (pre 6.4.2.19; MD5)
    cti_displaytest
        config: cti.displaytest
        type: bool
        vname: LBL_CTI_DISPLAYTEST
        vdesc: LBL_CTI_DISPLAYTEST_DESC
    cti_debug_modus
        config: cti.debugmodus
        type: bool
        vname: LBL_CTI_DEBUG
        vdesc: LBL_CTI_DEBUG_DESC
