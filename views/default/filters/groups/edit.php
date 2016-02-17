<?php

$entity = elgg_extract('entity', $vars);
$identifier = elgg_extract('identifier', $vars, 'groups');
$filter_context = elgg_extract('filter_context', $vars, 'index');

$tabs = [
	'index' => "$identifier/edit/$entity->guid",
	'settings' => "$identifier/edit/$entity->guid/settings",
];


foreach ($tabs as $tab => $url) {
	elgg_register_menu_item('filter', array(
		'name' => "$identifier:edit:$tab",
		'text' => elgg_echo("$identifier:edit:$tab"),
		'href' => elgg_normalize_url($url),
		'selected' => $tab == $filter_context,
	));
}

$params = $vars;
$params['sort_by'] = 'priority';
echo elgg_view_menu('filter', $params);