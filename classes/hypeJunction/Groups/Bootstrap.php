<?php

namespace hypeJunction\Groups;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {

	/**
	 * {@inheritdoc}
	 */
	public function load(): void {
		$autoloader = dirname(__DIR__, 3) . '/autoloader.php';
		if (file_exists($autoloader)) {
			require_once $autoloader;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot(): void {
		$svc = elgg()->groups;
		/* @var $svc \hypeJunction\Groups\GroupsService */

		$svc->registerSubtype('group', [
			'site_menu' => true,
			'labels' => [
				'en' => [
					'item' => 'Group',
					'collection' => 'Groups',
				],
			],
			'root' => true,
			'parents' => ['group'],
			'identifier' => 'groups',
		]);

		elgg_register_event_handler('init', 'system', function () {
			$svc = elgg()->groups;
			/* @var $svc \hypeJunction\Groups\GroupsService */

			$svc->setup();
		}, 800);
	}

	/**
	 * {@inheritdoc}
	 */
	public function init(): void {
		elgg_register_event_handler('permissions_check', 'group', ConfigureEditPermissions::class);
		elgg_register_event_handler('container_permissions_check', 'group', ConfigureContainerPermissions::class);

		elgg_unregister_event_handler('register', 'menu:page', '_groups_page_menu');
		elgg_unregister_event_handler('register', 'menu:page', '_groups_page_menu_group_profile');
		elgg_register_event_handler('register', 'menu:filter:groups/all', GroupsTabs::class, 800);
		elgg_register_event_handler('register', 'menu:filter:collection/all', CollectionTabs::class);
		elgg_register_event_handler('register', 'menu:filter:collection/owner', CollectionTabs::class);
		elgg_register_event_handler('register', 'menu:owner_block', OwnerBlockMenu::class);
		elgg_register_event_handler('register', 'menu:entity', EntityMenu::class);

		elgg_register_event_handler('fields', 'group', SetGroupFields::class, 100);

		elgg_register_event_handler('tool_options', 'group', SetupGroupTools::class, 800);

		elgg_extend_view('groups/sidebar/members', 'groups/sidebar/admins', 100);
		elgg_extend_view('groups/groups.css', 'groups/extras.css');

		elgg_register_event_handler('commands', 'cli', RegisterCliCommands::class);
	}
}
