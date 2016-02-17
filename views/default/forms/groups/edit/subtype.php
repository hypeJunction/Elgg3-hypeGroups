<?php

$entity = elgg_extract('entity', $vars);
if ($entity && $entity->guid) {
	return;
}

$subtype = elgg_extract('subtype', $vars);
echo elgg_view('input/hidden', array(
	'name' => 'subtype',
	'value' => $subtype,
));
