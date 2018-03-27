<?php

$request = elgg_extract('request', $vars);
/* @var $request \Elgg\Request */

$selected_tab = $request->getParam('filter');
if ($selected_tab) {
	$content = elgg_view("groups/listing/$selected_tab", $vars);

	$title = elgg_echo('groups:all');

	$body = elgg_view_layout('default', [
		'content' => $content,
		'sidebar' => $sidebar,
		'title' => $title,
		'filter_id' => 'groups/all',
		'filter_value' => $selected_tab,
	]);

	echo elgg_view_page($title, $body);
} else {
	echo elgg_view('resources/collection/all', $vars);
}