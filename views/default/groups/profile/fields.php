<?php
$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggEntity) {
	return;
}

$svc = elgg()->{'posts.model'};
/* @var $svc \hypeJunction\Post\Model */

$fields = $svc->getFields($entity, \hypeJunction\Fields\Field::CONTEXT_PROFILE);

$output = '';

$output .= elgg_view('output/longtext', [
	'value' => $entity->description,
	'class' => 'groups-description',
]);

foreach ($fields as $field) {
	/* @var $field \hypeJunction\Fields\FieldInterface */
	$output .= $field->output($entity);
}

echo $output;