<?php ?>
acceptable_sugar_versions
	regex_matches
		- 7\.[15]\.[1-9]+
name: CTI module
description: Starface / Asterisk CTI Appliance integration
author: visual4 GmbH - BR
published_date: 2014-04-28
version: 2.2.6
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
