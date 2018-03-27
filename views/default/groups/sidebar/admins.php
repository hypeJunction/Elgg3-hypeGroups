<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggGroup) {
	return;
}

$svc = elgg()->groups;
/* @var $svc \hypeJunction\Groups\GroupsService */

$body = elgg_view_entity_list($svc->getGroupAdmins($entity), [
	'full_view' => false,
]);

echo elgg_view('groups/profile/module', [
	'content' => $body,
	'title' => elgg_echo('groups:admins'),
	'class' => 'has-list',
]);
