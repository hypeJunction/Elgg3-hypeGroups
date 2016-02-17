<?php

elgg_gatekeeper();

$identifier = elgg_extract('identifier', $vars, 'groups');
$segments = elgg_extract('segments', $vars);

$guid = elgg_extract('guid', $vars);
$group = get_entity($guid);
$vars['entity'] = $group;

if (!$group instanceof ElggGroup || !$group->canEdit()) {
	register_error(elgg_echo('groups:noaccess'));
	forward(REFERRER);
}

// pushing context to make it easier to user 'menu:filter' hook
elgg_push_context("$identifier/edit");

elgg_load_library('elgg:groups');

elgg_set_page_owner_guid($group->guid);
group_subtypes_configure_tools($group->getSubtype());

$tab = array_shift($segments);
if (!$tab || !elgg_view_exists("resources/groups/edit/$tab")) {
	$tab = 'index';
}

echo elgg_view_resource("groups/edit/$tab", $vars);
