<?php

namespace hypeJunction\Groups;

use Elgg\Hook;
use hypeJunction\Lists\CollectionInterface;

class OwnerBlockMenu {

	public function __invoke(Hook $hook) {

		$menu = $hook->getValue();

		$owner = $hook->getEntityParam();

		$svc = elgg()->groups;
		/* @var $svc \hypeJunction\Groups\GroupsService */

		if ($owner instanceof \ElggUser && $owner->canEdit()) {
			$subtypes = $svc->all();

			$identifiers = array_map(function($e) {
				return $e->identifier;
			}, $subtypes);

			$child_menu = [];
			if (elgg_is_active_plugin('hypeHero')) {
				$child_menu = [
					'display' => 'dropdown',
					'class' => 'elgg-menu-hover',
					'data-position' => json_encode([
						'at' => 'right bottom',
						'my' => 'right top',
						'collision' => 'fit fit',
					]),
					'id' => 'groups-membership-menu',
				];
			}

			$menu[] = \ElggMenuItem::factory([
				'name' => 'groups',
				'text' => elgg_echo('groups'),
				'href' => 'javascript:',
				'child_menu' => $child_menu,
				'selected' => in_array($svc->getPageIdentifier(), $identifiers),
			]);

			foreach ($subtypes as $subtype => $conf) {

				$joined = $svc->getJoinedGroups($owner, [
					'count' => true,
					'subtypes' => $subtype,
				]);

				if ($joined) {
					$menu[] = \ElggMenuItem::factory([
						'name' => "groups:$subtype",
						'parent_name' => 'groups',
						'text' => elgg_echo("{$conf->identifier}:yours"),
						'href' => elgg_generate_url("collection:group:$subtype:member", [
							'username' => $owner->username,
						]),
						'badge' => $joined,
					]);

					$owned = $svc->getAdministeredGroups($owner, [
						'count' => true,
						'subtypes' => $subtype,
					]);

					if ($owned) {
						$menu[] = \ElggMenuItem::factory([
							'name' => "groups:$subtype:owned",
							'parent_name' => "groups",
							'text' => elgg_echo("{$conf->identifier}:owned"),
							'href' => elgg_generate_url("collection:group:$subtype:owner", [
								'username' => $owner->username,
							]),
							'badge' => $owned,
						]);
					}
				} else if ($owner->canWriteToContainer(0, 'group', $subtype)) {
					$menu[] = \ElggMenuItem::factory([
						'name' => "groups:$subtype",
						'parent_name' => 'groups',
						'text' => elgg_echo("add:group:$subtype"),
						'href' => elgg_generate_url("add:group:$subtype", [
							'container_guid' => $owner->guid,
						]),
						'icon' => 'plus',
					]);
				}
			}
		}

		return $menu;
	}
}