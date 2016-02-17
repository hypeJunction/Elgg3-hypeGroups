<?php

$subtypes = get_registered_entity_types('group');
if (empty($subtypes)) {
	return;
}

$subtype_options = array('' => '');
foreach ($subtypes as $subtype) {
	$subtype_options[$subtype] = elgg_echo("group:$subtype");
}

$dbprefix = elgg_get_config('dbprefix');
$options = array(
	'selects' => array('ge.name AS name'),
	'types' => 'group',
	'limit' => 100,
	'joins' => array(
		"JOIN {$dbprefix}groups_entity ge ON ge.guid = e.guid",
	),
	'wheres' => array(
		"e.subtype = 0",
	),
	'order_by' => 'ge.name ASC',
	'callback' => false,
	'count' => true,
);

$count = elgg_get_entities($options);
if (!$count) {
	return;
}

$options['count'] = false;
$groups = new ElggBatch('elgg_get_entities', $options);

$table = '<table class="elgg-table-alt">';
foreach ($groups as $group) {
	$table .= '<tr>';
	$table .= '<td>' . $group->name . '</td>';
	$table .= '<td>' . elgg_view('input/select', array(
				'name' => "groups[$group->guid]",
				'options_values' => $subtype_options,
			)) . '</td>';
	$table .= '</tr>';
}
$table .= '</table>';

echo elgg_view_module('info', elgg_echo('admin:groups:subtypes:change_subtype'), $table, array(
	'class' => 'groups-subtypes-config-module',
));

echo elgg_view('input/submit');
