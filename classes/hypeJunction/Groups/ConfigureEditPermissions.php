<?php

namespace hypeJunction\Groups;

use Elgg\Hook;

class ConfigureEditPermissions {

	/**
	 * Setup group edit permissions
	 *
	 * @param Hook $hook Hook
	 *
	 * @return bool
	 */
	public function __invoke(Hook $hook) {

		$group = $hook->getEntityParam();
		$user = $hook->getUserParam();

		if (!$group instanceof \ElggGroup || $user instanceof \ElggUser) {
			return null;
		}

		if (check_entity_relationship($user->guid, 'group_admin', $group->guid)) {
			return true;
		}
	}
}