<?php

elgg_load_library('elgg:groups');

$entity = elgg_extract('entity', $vars, null);

$form_vars = array(
	'enctype' => 'multipart/form-data',
	'class' => 'elgg-form-alt',
);

$view_vars = groups_prepare_form_vars($entity);
if (is_array($view_vars)) {
	$view_vars = array_merge($vars, $view_vars);
}

if ($entity instanceof ElggGroup) {
	$subtype = $entity->getSubtype();
} else {
	$subtype = elgg_extract('subtype', $view_vars);
}

if ($subtype && elgg_view_exists("forms/groups/edit/$subtype") && elgg_action_exists("groups/edit/$subtype")) {
	echo elgg_view_form("groups/edit/$subtype", $form_vars, $view_vars);
} else {
	echo elgg_view_form('groups/edit', $form_vars, $view_vars);
}
?>
<script>
	require(['elgg/groups/edit']);
</script>