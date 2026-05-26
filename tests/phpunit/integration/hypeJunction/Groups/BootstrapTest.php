<?php

namespace hypeJunction\Groups;

use Elgg\IntegrationTestCase;

class BootstrapTest extends IntegrationTestCase {

	public function up(): void {}

	public function down(): void {}

	public function getPluginID(): string {
		return 'hypegroups';
	}

	public function testPluginIsActive(): void {
		$plugin = \elgg_get_plugin_from_id('hypegroups');
		$this->assertNotNull($plugin);
		$this->assertTrue($plugin->isActive());
	}

	public function testGroupsServiceRegisteredInContainer(): void {
		$svc = elgg()->groups;
		$this->assertInstanceOf(GroupsService::class, $svc);
	}

	public function testEditPermissionsEventRegistered(): void {
		$events = \_elgg_services()->events;
		$this->assertTrue($events->hasHandler('permissions_check', 'group'));
	}

	public function testContainerPermissionsEventRegistered(): void {
		$events = \_elgg_services()->events;
		$this->assertTrue($events->hasHandler('container_permissions_check', 'group'));
	}

	public function testGroupFieldsEventRegistered(): void {
		$events = \_elgg_services()->events;
		$this->assertTrue($events->hasHandler('fields', 'group'));
	}

	public function testToolOptionsEventRegistered(): void {
		$events = \_elgg_services()->events;
		$this->assertTrue($events->hasHandler('tool_options', 'group'));
	}
}
