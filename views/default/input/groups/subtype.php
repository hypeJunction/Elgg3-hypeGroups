<?php

$identifier = elgg_extract('identifier', $vars, 'groups');

$parent_guid = elgg_extract('parent_guid', $vars);
if (!$parent_guid) {
	$parent_guid = elgg_get_logged_in_user_guid();
}

$parent = get_entity($parent_guid);

$subtypes = group_subtypes_get_allowed_subtypes_for_parent($parent);
if (empty($subtypes)) {
	echo elgg_format_element('p', ['class' => 'elgg-no-results'], elgg_echo("$identifier:no_allowed_subtypes"));
	return;
}

$options_values = array();
foreach ($subtypes as $subtype) {
	$options_values[$subtype] = elgg_echo("group:$subtype");
}

echo elgg_view('input/select', array(
	'name' => 'subtype',
	'value' => elgg_extract('value', $vars),
	'options_values' => $options_values,
));
