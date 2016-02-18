<?php

$config = group_subtypes_get_config();

$dbprefix = elgg_get_config('dbprefix');
$query = "SELECT subtype FROM {$dbprefix}entity_subtypes WHERE type = 'group'";
$data = get_data($query);
foreach ($data as $row) {
	if (!isset($config[$row->subtype])) {
		$config[$row->subtype] = array();
	}
}

foreach ($config as $subtype => $options) {
	$mod = elgg_view('forms/admin/groups/subtypes/subtype', array(
		'subtypes' => array_keys($config),
		'subtype' => $subtype,
		'options' => $options,
	));
	echo elgg_view_module('info', elgg_echo("group:$subtype"), $mod, array(
		'class' => 'groups-subtypes-config-module',
	));
}


echo elgg_view('input/submit');

