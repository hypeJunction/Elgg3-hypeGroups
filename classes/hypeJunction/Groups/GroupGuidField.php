<?php

namespace hypeJunction\Groups;

use ElggEntity;
use hypeJunction\Fields\Field;
use Symfony\Component\HttpFoundation\ParameterBag;

class GroupGuidField extends Field {

	/**
     * @param ElggEntity $entity
     * @param ParameterBag $parameters
     */
    public function save(ElggEntity $entity, ParameterBag $parameters) {

	}

	/**
     * @param ElggEntity $entity
     * @return mixed
     */
    public function retrieve(ElggEntity $entity) {
		return $entity->guid;
	}

}