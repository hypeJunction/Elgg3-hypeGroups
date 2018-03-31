<?php

namespace hypeJunction\Groups;

use Elgg\Hook;

class ConfigureContainerPermissions {

	/**
	 * Restrict group creation
	 *
	 * @param Hook $hook Hook
	 *
	 * @return bool
	 */
	public function __invoke(Hook $hook) {
		if (elgg_get_plugin_setting('limited_groups', 'groups') == 'yes' && !elgg_is_admin_logged_in()) {
			return false;
		}
	}
}