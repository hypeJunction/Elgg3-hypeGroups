<?php

namespace hypeJunction\Groups;

use Elgg\Event;

/**
 * Sets up group tool registrations.
 */
class SetupGroupTools {

	/**
	 * Filter and label group tools for a given group subtype.
	 *
	 * @param Event $event Group tools event
	 *
	 * @return \Elgg\Collections\Collection|null
	 */
	public function __invoke(Event $event) {

		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggGroup) {
			return;
		}

		$tools = $event->getValue();
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

			if (is_array($config->tools) && !in_array($tool->name, $config->tools)) {
				return false;
			}

			return true;
		});

		return $tools;
	}
}
