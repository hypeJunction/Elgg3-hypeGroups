<?php

$identifier = elgg_extract('identifier', $vars, 'groups');
$group = elgg_extract('entity', $vars);

$title = elgg_echo("$identifier:edit");

elgg_push_breadcrumb($group->getDisplayName(), $group->getURL());
elgg_push_breadcrumb($title, "$identifier/edit/$group->guid");
elgg_push_breadcrumb(elgg_echo("$identifier:edit:settings"));

$vars['filter_context'] = 'settings';
$filter = elgg_view('filters/groups/edit', $vars);
$content = elgg_view('groups/edit', $vars);
if (!$content) {
	$content = elgg_format_element('p', ['class' => 'elgg-no-results'], elgg_echo("$identifier:settings_no_results"));
}

$body = elgg_view_layout('content', array(
	'content' => $content,
	'title' => $title,
	'filter' => $filter ? : '',
		));

echo elgg_view_page($title, $body);

