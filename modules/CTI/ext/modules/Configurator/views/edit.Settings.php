<?php return; ?>
layout
	sections
		--
			id: cti
			vname: LBL_CTI_SETTINGS
			columns: 1
			show_descriptions: true
			elements
                - cti_adapter
                --
                    id: starface_body
                    section: true
                    toggle_display
                        name: cti_adapter
                        value: starface
                    elements              
                        - cti_host
                        - cti_uri
                        - cti_port
                        - cti_https
                        - cti_callback_host
                        - cti_callback_uri
                        - cti_callback_port
                        - cti_callback_https
                --
                    id: asterisk_body
                    section: true
                    toggle_display
                        name: cti_adapter
                        value: asterisk
                    elements              
                        - cti_asterisk_host
                        - cti_asterisk_port
                        - cti_asterisk_user
                        - cti_asterisk_password
