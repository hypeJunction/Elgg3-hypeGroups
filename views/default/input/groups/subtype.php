<?php

$identifier = elgg_extract('identifier', $vars, 'groups');

$parent_guid = elgg_extract('parent_guid', $vars);
if (!$parent_guid) {
	$parent_guid = elgg_get_logged_in_user_guid();
}

$parent = get_entity($parent_guid);

$options_values = array();
$subtypes = get_registered_entity_types('group');

foreach ($subtypes as $subtype) {
	$params = array(
		'parent' => $parent,
		'type' => 'group',
		'subtype' => $subtype,
	);
	$can_parent = elgg_trigger_plugin_hook('permissions_check:parent', 'group', $params, true);
	if ($can_parent) {
		$options_values[$subtype] = elgg_echo("group:$subtype");
	}
}

if (empty($options_values)) {
	echo elgg_format_element('p', ['class' => 'elgg-no-results'], elgg_echo("$identifier:no_allowed_subtypes"));
	return;
}

echo elgg_view('input/select', array(
	'name' => 'subtype',
	'value' => elgg_extract('value', $vars),
	'options_values' => $options_values,
));
