<?php

namespace hypeJunction\Groups;

use Elgg\Hook;

class GroupsTabs {

	/**
	 * @elgg_plugin_hook register menu:filter:groups/all
	 *
	 * @param Hook $hook
	 *
	 * @return mixed|null
	 */
	public function __invoke(Hook $hook) {

		$tabs = $hook->getValue();

		$remove = [
			'newest',
			'alpha',
			'popular',
			'featured',
		];

		foreach ($tabs as $key => $tab) {
			if (in_array($tab->getName(), $remove)) {
				unset($tabs[$key]);
				continue;
			}
		}

		$svc = elgg()->groups;
		/* @var $svc \hypeJunction\Groups\GroupsService */

		$identifier = $svc->getPageIdentifier();
		$subtypes = $svc->getSubtypes($identifier);
		$subtype = array_shift($subtypes);

		$tabs[] = \ElggMenuItem::factory([
			'name' => 'groups:all',
			'text' => elgg_echo('all'),
			'href' => elgg_generate_url("collection:group:$subtype:all"),
			'priority' => 100,
		]);

		$user = elgg_get_logged_in_user_entity();
		if ($user) {
			$tabs[] = \ElggMenuItem::factory([
				'name' => 'groups:mine',
				'text' => elgg_echo('mine'),
				'href' => elgg_generate_url("collection:group:$subtype:member", [
					'username' => $user->username,
				]),
				'priority' => 200,
			]);
		}

		return $tabs;
	}
}