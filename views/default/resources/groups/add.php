<?php

elgg_gatekeeper();

if (elgg_get_plugin_setting('limited_groups', 'groups') == 'yes' && !elgg_is_admin_logged_in()) {
	register_error(elgg_echo('groups:cantcreate'));
	forward('', '403');
}

$identifier = elgg_extract('identifier', $vars, 'groups');
$parent_guid = elgg_extract('parent_guid', $vars);
$subtype = elgg_extract('subtype', $vars, get_input('subtype'));

if (!$parent_guid) {
	$parent_guid = elgg_get_logged_in_user_guid();
}

$parent = get_entity($parent_guid);
if (!$parent || !$parent->canWriteToContainer(0, 'group', $subtype)) {
	register_error(elgg_echo("$identifier:illegal_parent"));
	forward(REFERRER);
}

// pushing context to make it easier to user 'menu:filter' hook
elgg_push_context("$identifier/add");

$vars['parent_guid'] = $parent->guid;
$vars['subtype'] = $subtype;

elgg_set_page_owner_guid($parent_guid);

if (!$subtype) {
	$allowed_subtypes = group_subtypes_get_allowed_subtypes_for_parent($parent);
	if (empty($allowed_subtypes)) {
		register_error(elgg_echo("$identifier:no_allowed_subtypes"));
		forward(REFERRER);
	}
	if (count($allowed_subtypes) == 1) {
		$subtype = $allowed_subtypes[0];
	}
}

if ($subtype) {
	// can write to container ignores hierarchy logic
	$params = array(
		'parent' => $parent,
		'type' => 'group',
		'subtype' => $subtype,
	);
	$can_contain = elgg_trigger_plugin_hook('permissions_check:parent', 'group', $params, true);
	if (!$can_contain) {
		register_error(elgg_echo("$identifier:illegal_subtype", array(elgg_echo("item:group:$subtype"))));
		forward(REFERRER);
	}

	group_subtypes_configure_tools($subtype);
	$title = elgg_echo("$identifier:add:$subtype");
	$content = elgg_view('groups/edit/index', $vars);
} else {
	$title = elgg_echo("$identifier:add:select_subtype");
	$content = elgg_view_form('groups/add/select_subtype', array(
		'action' => current_page_url(),
		'method' => 'GET',
		'disable_security' => true,
			), $vars);
}

elgg_push_breadcrumb(elgg_echo($identifier), "$identifier/all");
if ($parent instanceof ElggGroup) {
	elgg_push_breadcrumb($parent->getDisplayName(), $parent->getURL());
}
elgg_push_breadcrumb($title);

$body = elgg_view_layout('content', $params = array(
	'content' => $content,
	'title' => $title,
	'filter' => '',
		));

echo elgg_view_page($title, $body);
