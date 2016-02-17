<?php

$params = get_input('params', array());

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
}

elgg_set_plugin_setting('config', serialize($params), 'group_subtypes');

system_message(elgg_echo('admin:groups:subtypes:config:saved'));
