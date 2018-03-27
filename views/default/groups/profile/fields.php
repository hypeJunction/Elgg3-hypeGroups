<?php
$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggEntity) {
	return;
}

$fields = elgg()->{'posts.model'}->getProfileFields($entity, $vars);

$output = '';

$output .= elgg_view('output/longtext', [
	'value' => $entity->description,
	'class' => 'groups-description',
]);

foreach ($fields as $field) {
	$output .= elgg_view("post/output/field", $field);
}

echo $output;