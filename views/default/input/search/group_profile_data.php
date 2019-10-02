<?php

$value = elgg_extract('value', $vars, []);

$field_obj = elgg_extract('field', $vars);
/* @var $field_obj \hypeJunction\Lists\SearchFieldInterface */

$entity_types = (array) $field_obj->getCollection()->getType();
$entity_subtypes = (array) $field_obj->getCollection()->getSubtypes();

$search_fields = new \hypeJunction\Fields\Collection();

foreach ($entity_types as $type) {
	foreach ($entity_subtypes as $subtype) {
		$class = elgg_get_entity_class($type, $subtype);
		if (!$class) {
			continue;
		}

		$entity = new $class();

		$svc = elgg()->{'posts.model'};
		/* @var $svc \hypeJunction\Post\Model */

		$fields = $svc->getFields($entity);

		$fields = $fields->filter(function (\hypeJunction\Fields\FieldInterface $field) {
			return (bool) $field->is_search_field;
		});

		foreach ($fields as $field) {
			$search_fields->add($field->name, $field);
		}
	}
}

foreach ($search_fields as $field) {
	/* @var $field \hypeJunction\Fields\FieldInterface */

	$field->entity_types = $entity_types;
	$field->entity_subtypes = $entity_subtypes;

	if ($field->search_type) {
		$field->type = $field->search_type;
	}

	$field->input_name = "profile[$field->name]";
	$field->value = elgg_extract($field->name, $value);
	$field->{'#help'} = '';
	
	echo $field->render($entity);
}