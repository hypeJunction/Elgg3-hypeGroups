<?php

set_time_limit(0);

$i = 0;
$dbprefix = elgg_get_config('dbprefix');
$groups = get_input('groups');
foreach ($groups as $guid => $subtype) {
	if (!$subtype) {
		continue;
	}
	$target_subtype_id = get_subtype_id('group', $subtype);
	if (!$target_subtype_id) {
		continue;
	}

	// Update entities table
	$query = "UPDATE {$dbprefix}entities SET subtype=$target_subtype_id WHERE guid=$guid";
	update_data($query);

	// Update river table
	$query = "UPDATE {$dbprefix}river SET subtype='$subtype' WHERE object_guid=$guid";
	update_data($query);

	// Update system log table
	$target_class = get_subtype_class_from_id($target_subtype_id) ? : 'ElggGroup';
	$query = "UPDATE {$dbprefix}system_log SET object_subtype='$subtype', object_class='$target_class' WHERE object_id='$guid'";
	update_data($query);

	$i++;
}

system_message(elgg_echo('admin:groups:subtypes:change_subtype:success', array($i)));