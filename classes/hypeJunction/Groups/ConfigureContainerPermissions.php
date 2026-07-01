<?php

namespace hypeJunction\Groups;

use Elgg\Event;

/**
 * Configures container permission hooks for groups.
 */
class ConfigureContainerPermissions {

	/**
	 * Restrict group creation
	 *
	 * @param Event $event Hook
	 *
	 * @return bool
	 */
	public function __invoke(Event $event) {
		if (elgg_get_plugin_setting('limited_groups', 'groups') == 'yes' && !elgg_is_admin_logged_in()) {
			return false;
		}
	}
}
