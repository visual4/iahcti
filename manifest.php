<?php ?>
acceptable_sugar_versions
	regex_matches
		- 7\.5\.[1-9]+
name: CTI module
description: Starface / Asterisk CTI Appliance integration
author: visual4 GmbH - BR
published_date: 2014-07-21
version: 2.7.5.9-8
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
