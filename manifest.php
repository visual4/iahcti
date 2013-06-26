<?php ?>
acceptable_sugar_versions
	regex_matches
		- 7\.[01]\.[1-9]+
name: CTI module
description: Starface / Asterisk CTI Appliance integration
author: visual4 GmbH - BR
published_date: 2013-06-26
version: 2.2.3
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
