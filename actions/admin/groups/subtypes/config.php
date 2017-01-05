<?php

$params = get_input('params', array());

$tools = (array) elgg_get_config("group_tool_options");
$tool_options = [];
foreach ($tools as $tool) {
	$tool_options[] = $tool->name;
}

foreach ($params as $subtype => $options) {
	if (empty($options['identifier'])) {
		$options['identifier'] = 'groups';
	}
	if (empty($options['tools'])) {
		$options['tools'] = array();
	}
	if (empty($options['parents'])) {
		$options['parents'] = array();
	}
	$options['root'] = (bool) $options['root'];
	$options['preset_tools'] = (bool) $options['preset_tools'];

	$params[$subtype] = $options;

	// disable tools so we don't have erranous menu items and modules displayed
	$groups = elgg_get_entities([
		'types' => 'group',
		'subtypes' => $subtype,
		'limit' => 0,
		'batch' => true,
	]);

	foreach ($groups as $group) {
		foreach ($tool_options as $tool) {
			if (!in_array($tool, $options['tools'])) {
				$group->{"{$tool}_enable"} = 'no';
			}
		}
		if ($options['preset_tools']) {
			foreach ($options['tools'] as $tool) {
				$group->{"{$tool}_enable"} = 'yes';
			}
		}
	}
}

elgg_set_plugin_setting('config', serialize($params), 'group_subtypes');

system_message(elgg_echo('admin:groups:subtypes:config:saved'));
