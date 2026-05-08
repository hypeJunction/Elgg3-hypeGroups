<?php

namespace hypeJunction\Groups;

use Elgg\Event;

class ConfigureEditPermissions {

	/**
	 * Setup group edit permissions
	 *
	 * @param Event $event Hook
	 *
	 * @return bool
	 */
	public function __invoke(Event $event) {

		$group = $event->getEntityParam();
		$user = $event->getUserParam();

		if (!$group instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return null;
		}

		if ($user->hasRelationship($group->guid, 'group_admin')) {
			return true;
		}
	}
}