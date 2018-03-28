<?php

namespace hypeJunction\Groups;

use ElggEntity;
use hypeJunction\Fields\Field;
use Symfony\Component\HttpFoundation\ParameterBag;

class GroupGuidField extends Field {

	public function save(ElggEntity $entity, ParameterBag $parameters) {

	}

	public function retrieve(ElggEntity $entity) {
		return $entity->guid;
	}

}