<?php

$type = 'group';
$subtype = elgg_extract('subtype', $vars, 'group');

$class = elgg_get_entity_class($type, $subtype);
if (!$class) {
	throw new \Elgg\BadRequestException();
}

$container_guid = elgg_extract('container_guid', $vars);
elgg_entity_gatekeeper($container_guid);

$container = get_entity($container_guid);
if (!$container || !$container->canWriteToContainer(0, $type, $subtype)) {
	throw new \Elgg\EntityPermissionsException();
}

$entity = new $class();
if (!$entity instanceof \ElggEntity) {
	throw new \Elgg\BadRequestException();
}

$entity->container_guid = $container->guid;

elgg_push_collection_breadcrumbs($type, $subtype, $container);

$svc = elgg()->{'posts.model'};
/* @var $svc \hypeJunction\Post\Model */

$vars = $svc->getFormVars($entity, $vars);
$vars['context'] = \hypeJunction\Fields\Field::CONTEXT_CREATE_FORM;

if (elgg_action_exists("groups/edit/$subtype")) {
	$action = "groups/edit/$subtype";
} else {
	$action = "groups/edit";
}

$content = elgg_view_form('post/save', [
	'enctype' => 'multipart/form-data',
	'class' => 'post-form',
	'action' => elgg_generate_action_url($action),
], $vars);

if (elgg_is_xhr()) {
	echo $content;
	return;
}

$layout = elgg_view_layout('default', [
	'header' => false,
	'content' => $content,
	'sidebar' => false,
	'filter' => $action,
]);

echo elgg_view_page(null, $layout);
