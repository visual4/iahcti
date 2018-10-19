<?php return; ?>

hooks
    before_save
        --
            class_function: user_before_save
			class: v4_cti_hooks
            file: modules/CTI/v4_cti_hooks.php
    page_init
        --
            class_function: page_init
            class: v4_cti_hooks
            file: modules/CTI/v4_cti_hooks.php
fields
	cti_user_id
		type: varchar
		vname: LBL_CTI_USER_ID
	cti_password
		type: password
		vname: LBL_CTI_PASSWORD
    cti_hash
        type: varchar
        vname: LBL_CTI_HASH