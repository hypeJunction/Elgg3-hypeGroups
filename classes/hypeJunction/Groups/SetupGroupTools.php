<?php

namespace hypeJunction\Groups;

use Elgg\Hook;

class SetupGroupTools {

	public function __invoke(Hook $hook) {

		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return;
		}

		$tools = $hook->getValue();

		$svc = elgg()->groups;
		/* @var $svc \hypeJunction\Groups\GroupsService */

		$subtype = $entity->getSubtype();
		$config = $svc->$subtype;

		if (!$config) {
			return;
		}

//		if ($config->identifier && $config->identifier != 'groups') {
//			foreach ($tools as &$tool) {
//				$tool->label = elgg_echo("{$config->identifier}:tool:{$tool->name}");
//			}
//		}

		if (is_array($config->tools)) {
			foreach ($tools as $key => &$tool) {
				if (!in_array($tool->name, $config->tools)) {
					unset($tools[$key]);
				} else {
					$tool->default_on = true;
				}
			}
		}

		return $tools;
	}
}