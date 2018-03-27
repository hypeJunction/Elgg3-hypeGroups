<?php

$guid = elgg_extract('guid', $vars);
elgg_entity_gatekeeper($guid, 'group');

$entity = get_entity($guid);
if (!$entity instanceof \ElggEntity) {
	throw new \Elgg\BadRequestException();
}

if (!$entity->canEdit()) {
	throw new \Elgg\EntityPermissionsException();
}

elgg_push_entity_breadcrumbs($entity);
elgg_push_breadcrumb($title);

$subtype = $entity->getSubtype();

if (elgg_action_exists("groups/edit/$subtype")) {
	$action = "groups/edit/$subtype";
} else {
	$action = "groups/edit";
}

$content = elgg_view_form('post/save', [
	'enctype' => 'multipart/form-data',
	'class' => 'post-form',
	'actions' => elgg_generate_action_url($action),
], elgg()->{'posts.model'}->getFormVars($entity, $vars));

if (elgg_is_xhr()) {
	echo $content;
	return;
}

$layout = elgg_view_layout('default', [
	'header' => false,
	'content' => $content,
	'sidebar' => false,
	'filter' => $action,
	'target' => $entity,
]);

echo elgg_view_page($entity->getDisplayName(), $layout);
