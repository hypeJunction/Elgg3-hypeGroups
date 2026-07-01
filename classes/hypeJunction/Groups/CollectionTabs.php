<?php

namespace hypeJunction\Groups;

use Elgg\Event;
use hypeJunction\Lists\Collection;
use hypeJunction\Lists\CollectionInterface;

/**
 * Builds tabs for group collection pages.
 */
class CollectionTabs {

	/**
	 * Register collection/all menu filter tabs.
	 *
	 * @elgg_plugin_hook register menu:filter:collection/all
	 *
	 * @param Event $event Menu event
	 *
	 * @return mixed|null
	 */
	public function __invoke(Event $event) {

		$collection = $event->getParam('collection');

		if (!$collection instanceof DefaultGroupCollection || !in_array($collection->getCollectionType(), ['all', 'member'])) {
			return null;
		}

		if ($collection->getCollectionType() === 'member' && $collection->getTarget()->guid != elgg_get_logged_in_user_guid()) {
			return null;
		}

		return elgg_trigger_event_results('register', 'menu:filter:groups/all', $event->getParams(), $event->getValue());
	}
}
