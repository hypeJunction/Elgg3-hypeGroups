<?php

namespace hypeJunction\Groups;

use Elgg\Includer;
use Elgg\PluginBootstrap;

class Bootstrap extends PluginBootstrap {

	/**
	 * Get plugin root
	 * @return string
	 */
	protected function getRoot() {
		return $this->plugin->getPath();
	}

	/**
	 * {@inheritdoc}
	 */
	public function load() {
		Includer::requireFileOnce($this->getRoot() . '/autoloader.php');
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
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
	public function init() {
		elgg_register_plugin_hook_handler('permissions_check', 'group', ConfigureEditPermissions::class);
		elgg_register_plugin_hook_handler('container_permissions_check', 'group', ConfigureContainerPermissions::class);

		elgg_unregister_plugin_hook_handler('register', 'menu:page', '_groups_page_menu');
		elgg_unregister_plugin_hook_handler('register', 'menu:page', '_groups_page_menu_group_profile');
		elgg_register_plugin_hook_handler('register', 'menu:filter:groups/all', GroupsTabs::class);
		elgg_register_plugin_hook_handler('register', 'menu:filter:collection/all', CollectionTabs::class);
		elgg_register_plugin_hook_handler('register', 'menu:filter:collection/owner', CollectionTabs::class);
		elgg_register_plugin_hook_handler('register', 'menu:owner_block', OwnerBlockMenu::class);
		elgg_register_plugin_hook_handler('register', 'menu:entity', EntityMenu::class);

		elgg_register_plugin_hook_handler('fields', 'group', SetGroupFields::class, 100);

		elgg_register_plugin_hook_handler('tool_options', 'group', SetupGroupTools::class, 800);

		elgg_extend_view('groups/sidebar/members', 'groups/sidebar/admins', 100);
		elgg_extend_view('groups/groups.css', 'groups/extras.css');

		elgg_register_plugin_hook_handler('commands', 'cli', RegisterCliCommands::class);
	}

	/**
	 * {@inheritdoc}
	 */
	public function ready() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function shutdown() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function activate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function deactivate() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function upgrade() {

	}

}