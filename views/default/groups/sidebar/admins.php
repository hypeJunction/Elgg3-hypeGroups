<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggGroup) {
	return;
}

$svc = elgg()->groups;
/* @var $svc \hypeJunction\Groups\GroupsService */

$admins = $svc->getGroupAdmins($entity);

if (empty($admins)) {
	return;
}

$body = elgg_view_entity_list($admins, [
	'full_view' => false,
]);

echo elgg_view('groups/profile/module', [
	'content' => $body,
	'title' => elgg_echo('groups:admins'),
	'class' => 'has-list',
]);
