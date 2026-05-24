<?php

namespace hypeJunction\Groups;

use ElggEntity;
use hypeJunction\Fields\Field;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Group GUID input field.
 */
class GroupGuidField extends Field {

	/**
	 * {@inheritdoc}
	 */
	public function save(ElggEntity $entity, ParameterBag $parameters) {
	}

	/**
	 * {@inheritdoc}
	 */
	public function retrieve(ElggEntity $entity) {
		return $entity->guid;
	}
}
