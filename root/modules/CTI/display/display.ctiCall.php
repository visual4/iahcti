<?php return; ?>

list
    default_order_by: date_modified
filters
	current_user_only
		my_items: true
		vname: LBL_CURRENT_USER_FILTER
		field: assigned_user_id
    start
		operator: =
		where_function: search_by_date_start
	end
		operator: =
		where_function: search_by_date_end
    view_closed_items
        default_value: false
        type: flag
        negate_flag: true
        vname: LBL_VIEW_CLOSED_ITEMS
        field: state
        operator: not_eq
        value
            - HANGUP
basic_filters
    - view_closed_items
