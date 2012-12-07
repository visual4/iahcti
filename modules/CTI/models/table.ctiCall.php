<?php return; ?>
detail
	type: table
	table_name: cti_call
	primary_key: id
fields
	app.id
	app.date_modified
	cti_user
		type: varchar
		len: 100
	cti_id
		type: varchar
		len: 100
	state
		type: varchar
		len: 16
	caller_number
		type: varchar
	caller_name
		type: varchar
	called_number
		type: varchar
	called_name
		type: varchar
	timestamp
		type: varchar
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
			