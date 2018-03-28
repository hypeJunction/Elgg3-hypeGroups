<?php

namespace hypeJunction\Groups;

use Elgg\Request;
use ElggEntity;
use ElggGroup;
use hypeJunction\Fields\Field;
use Symfony\Component\HttpFoundation\ParameterBag;

class GroupToolsField extends Field {

	/**
	 * {@inheritdoc}
	 */
	public function isVisible(ElggEntity $entity, $context = null) {
		$svc = elgg()->groups;
		/* @var $svc \hypeJunction\Groups\GroupsService */

		$subtype = $entity->getSubtype();
		$config = $svc->$subtype;

		if ($config && $config->preset_tools) {
			return false;
		}

		return parent::isVisible($entity, $context);
	}

	/**
	 * {@inheritdoc}
	 */
	public function raw(Request $request, ElggEntity $entity) {
		/* @var $entity ElggGroup */
		$tools = elgg_get_group_tool_options($entity);

		$values = [];

		foreach ($tools as $tool) {
			$value = $request->getParam("{$tool->name}_enable");
			if (isset($value)) {
				$values[$tool->name] = $value === 'yes' ? true : false;
			} else {
				$values[$tool->name] = $value === 'yes' ? true : false;
			}
		}

		return $values;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(ElggEntity $entity, ParameterBag $parameters) {
		/* @var $entity ElggGroup */

		$value = $parameters->get($this->name);

		foreach ($value as $name => $enabled) {
			$enabled ? $entity->enableTool($name) : $entity->disableTool($name);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function retrieve(ElggEntity $entity) {
		/* @var $entity ElggGroup */

		$tools = elgg_get_group_tool_options($entity);

		$value = [];

		foreach ($tools as $tool) {
			$value["{$tool->name}_enable"] = $entity->isToolEnabled($tool->name) ? 'yes' : 'no';
		}

		return $value;
	}
}
