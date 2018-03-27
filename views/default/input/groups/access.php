<?php

$entity = elgg_extract('entity', $vars);

$value = (array) elgg_extract('value', $vars, []);
unset($vars['value']);

$vars = array_merge($vars, $value);

echo elgg_view('groups/edit/access', $vars);

if ($entity->guid) {
	echo elgg_view_field([
		'#type' => 'autocomplete',
		'#label' => elgg_echo('groups:administrators'),
		'#help' => elgg_echo('groups:administrators:help'),
		'match_on' => 'group_members',
		'name' => 'admin_guids[]',
		'multiple' => true,
		'value' => elgg_extract('admin_guids', $vars),
		'options' => [
			'group_guid' => $entity->guid,
		],
	]);
};