<?php

namespace hypeJunction\Groups;

use Elgg\Event;

/**
 * Builds tabs on the groups index page.
 */
class GroupsTabs {

	/**
	 * Register groups/all menu filter tabs.
	 *
	 * @elgg_plugin_hook register menu:filter:groups/all
	 *
	 * @param Event $event Menu event
	 *
	 * @return mixed|null
	 */
	public function __invoke(Event $event) {

		$tabs = $event->getValue();

		$remove = [
			'newest',
			'alpha',
			'popular',
			'featured',
		];

		foreach ($remove as $name) {
			$tabs->remove($name);
		}

		$svc = elgg()->groups;
		/* @var $svc \hypeJunction\Groups\GroupsService */

		$identifier = $svc->getPageIdentifier();
		$subtypes = $svc->getSubtypes($identifier);
		$subtype = array_shift($subtypes);

		$tabs->add(\ElggMenuItem::factory([
			'name' => 'groups:all',
			'text' => elgg_echo('all'),
			'href' => elgg_generate_url("collection:group:$subtype:all"),
			'priority' => 100,
		]));

		$user = elgg_get_logged_in_user_entity();
		if ($user) {
			$tabs->add(\ElggMenuItem::factory([
				'name' => 'groups:mine',
				'text' => elgg_echo('mine'),
				'href' => elgg_generate_url("collection:group:$subtype:member", [
					'username' => $user->username,
				]),
				'priority' => 200,
			]));
		}

		return $tabs;
	}
}
