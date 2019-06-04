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
		/* @var \Elgg\Collections\Collection|\Elgg\Groups\Tool[] */

		$svc = GroupsService::instance();

		$subtype = $entity->getSubtype();
		$config = $svc->$subtype;

		if (!$config) {
			return;
		}

		if ($config->identifier && $config->identifier != 'groups') {
			foreach ($tools as $tool) {
				$tool->label = elgg_echo("{$config->identifier}:tool:{$tool->name}");
			}
		}

		$tools = $tools->filter(function ($tool) use ($config) {
			if ($config->tools === false) {
				return false;
			}

			if (!in_array($tool->name, $config->tools)) {
				return false;
			}

			return true;
		});

		return $tools;
	}
}