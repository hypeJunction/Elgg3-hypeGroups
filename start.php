<?php

/**
 * hypeGroups
 *
 * @author    Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015-2018, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

return function () {

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

		elgg_register_plugin_hook_handler('permissions_check', 'group', \hypeJunction\Groups\ConfigureEditPermissions::class);
		elgg_register_plugin_hook_handler('container_permissions_check', 'group', \hypeJunction\Groups\ConfigureContainerPermissions::class);

		elgg_unregister_plugin_hook_handler('register', 'menu:page', '_groups_page_menu');
		elgg_unregister_plugin_hook_handler('register', 'menu:page', '_groups_page_menu_group_profile');
		elgg_register_plugin_hook_handler('register', 'menu:filter:groups/all', \hypeJunction\Groups\GroupsTabs::class);
		elgg_register_plugin_hook_handler('register', 'menu:filter:collection/all', \hypeJunction\Groups\CollectionTabs::class);
		elgg_register_plugin_hook_handler('register', 'menu:filter:collection/owner', \hypeJunction\Groups\CollectionTabs::class);
		elgg_register_plugin_hook_handler('register', 'menu:owner_block', \hypeJunction\Groups\OwnerBlockMenu::class);
		elgg_register_plugin_hook_handler('register', 'menu:entity', \hypeJunction\Groups\EntityMenu::class);

		elgg_register_plugin_hook_handler('fields', 'group', \hypeJunction\Groups\SetGroupFields::class, 100);

		elgg_register_plugin_hook_handler('tool_options', 'group', \hypeJunction\Groups\SetupGroupTools::class);

		elgg_extend_view('groups/sidebar/members', 'groups/sidebar/admins', 100);
		elgg_extend_view('groups/groups.css', 'groups/extras.css');
	});

	elgg_register_event_handler('init', 'system', function () {
		$svc = elgg()->groups;
		/* @var $svc \hypeJunction\Groups\GroupsService */

		$svc->setup();
	}, 800);
};