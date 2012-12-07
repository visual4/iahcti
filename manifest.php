<?php ?>
acceptable_sugar_versions
	regex_matches
		- 7\.[0]\.1[2-9]?
name: Starface module
description: Starface CTI Appliance integration
author: visual4 GmbH - BR
published_date: 2012-11-26
version: 2.1beta
type: module
is_uninstallable: true
id: CTIModule
copy
	--
		from: modules/CTI
		to: modules/CTI
	--
		from: root
		to: "/"
