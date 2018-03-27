<?php

namespace hypeJunction\Groups;

use Elgg\Hook;
use hypeJunction\Lists\Collection;
use hypeJunction\Lists\CollectionInterface;

class CollectionTabs {

	/**
	 * @elgg_plugin_hook register menu:filter:collection/all
	 *
	 * @param Hook $hook
	 *
	 * @return mixed|null
	 */
	public function __invoke(Hook $hook) {

		$collection = $hook->getParam('collection');

		if (!$collection instanceof DefaultGroupCollection || !in_array($collection->getCollectionType(), ['all', 'member'])) {
			return null;
		}

		if ($collection->getCollectionType() === 'member' && $collection->getTarget()->guid != elgg_get_logged_in_user_guid()) {
			return null;
		}

		return elgg_trigger_plugin_hook('register', 'menu:filter:groups/all', $hook->getParams(), $hook->getValue());
	}
}