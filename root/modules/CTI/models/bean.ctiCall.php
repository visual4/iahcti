<?php return; ?>
detail
	type: bean
    bean_file: modules/CTI/ctiCall.php
    unified_search: false
	table_name: cti_call
	primary_key: id
hooks
	before_save
		--
			class_function: validate
fields
	app.id
    app.date_entered
	app.date_modified
    app.modified_user
    app.assigned_user
    app.created_by_user
    app.deleted
	cti_user
		type: varchar
        vname: LBL_CTI_USER
		len: 100
	cti_id
        vname: LBL_CTI_ID
		type: varchar
		len: 100
	state
		type: enum
        vname: LBL_STATE
        options: cti_call_states_dom
	caller_number
        vname: LBL_CALLER_NUMBER
		type: phone
	caller_name
        vname: LBL_CALLER_NAME
		type: varchar
	called_number
        vname: LBL_CALLED_NUMBER
		type: phone
	called_name
        vname: LBL_CALLED_NAME
		type: varchar
	timestamp
        vname: LBL_TIMESTAMP
		type: varchar
    contact
        type: ref
        bean_name: Contact
        vname: LBL_CONTACT
    lead
        type: ref
        bean_name: Lead
        vname: LBL_LEAD
    start
        type: datetime
        vname: LBL_START
    end
        type: datetime
        vname: LBL_END
    duration
        type: duration
        vname: LBL_DURATION
    status
        type: status
        vname: LBL_STATUS
    log
        type: text
        vname: LBL_LOG
    direction
        type: varchar
        vname: LBL_DIRECTION
    lookup_number
        type: phone
        vname: LBL_LOOKUP_NUMBER
indices
	idx_cti_id
		fields
			- cti_id
	idx_cti_user
		fields
			- cti_user
			- state
	idx_timestamp
		fields
			- timestamp
    idx_crm_user
        fields
            - assigned_user_id
    idx_state
        fields
            - state
    idx_date
        fields
            - date_modified

			