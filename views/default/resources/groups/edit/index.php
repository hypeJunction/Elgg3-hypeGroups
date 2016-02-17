<?php

$identifier = elgg_extract('identifier', $vars, 'groups');
$group = elgg_extract('entity', $vars);

$title = elgg_echo("$identifier:edit");

elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());
elgg_push_breadcrumb($title, "$identifier/edit/$group->guid");
elgg_push_breadcrumb(elgg_echo("$identifier:edit:index"));

$vars['filter_context'] = 'index';
$filter = elgg_view('filters/groups/edit', $vars);
$content = elgg_view('groups/edit/index', $vars);

$body = elgg_view_layout('content', array(
	'content' => $content,
	'title' => $title,
	'filter' => $filter ? : '',
		));

echo elgg_view_page($title, $body);
