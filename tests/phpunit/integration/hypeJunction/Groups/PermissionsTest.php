<?php

namespace hypeJunction\Groups;

use Elgg\Event;
use Elgg\IntegrationTestCase;

class PermissionsTest extends IntegrationTestCase {

	public function up(): void {}

	public function down(): void {}

	public function getPluginID(): string {
		return 'hypegroups';
	}

	public function testGroupAdminCanEditGroup(): void {
		$owner = $this->createUser();
		$member = $this->createUser();
		$group = $this->createGroup(['owner_guid' => $owner->guid]);

		$member->addRelationship($group->guid, 'group_admin');

		$event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
		$event->method('getEntityParam')->willReturn($group);
		$event->method('getUserParam')->willReturn($member);
		$event->method('getValue')->willReturn(false);

		$handler = new ConfigureEditPermissions();
		$result = $handler($event);

		$this->assertTrue($result, 'Group admin should receive edit permission');
	}

	public function testNonAdminGroupMemberCannotEditViaAdminHook(): void {
		$owner = $this->createUser();
		$member = $this->createUser();
		$group = $this->createGroup(['owner_guid' => $owner->guid]);

		$event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
		$event->method('getEntityParam')->willReturn($group);
		$event->method('getUserParam')->willReturn($member);
		$event->method('getValue')->willReturn(false);

		$handler = new ConfigureEditPermissions();
		$result = $handler($event);

		$this->assertNull($result, 'Non-admin member should not receive edit permission from this handler');
	}

	public function testEditPermissionsHandlerIgnoresNonGroupEntities(): void {
		$user = $this->createUser();
		$object = $this->createObject(['subtype' => 'blog', 'owner_guid' => $user->guid]);

		$event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
		$event->method('getEntityParam')->willReturn($object);
		$event->method('getUserParam')->willReturn($user);
		$event->method('getValue')->willReturn(false);

		$handler = new ConfigureEditPermissions();
		$result = $handler($event);

		$this->assertNull($result, 'Handler should return null for non-group entities');
	}

	public function testContainerPermissionsReturnsFalseWhenLimitedGroupsEnabledForRegularUser(): void {
		$groups_plugin = \elgg_get_plugin_from_id('groups');
		if (!$groups_plugin) {
			$this->markTestSkipped('Core groups plugin not active');
		}

		$original = $groups_plugin->getSetting('limited_groups');
		$groups_plugin->setSetting('limited_groups', 'yes');

		$user = $this->createUser();
		\_elgg_services()->session_manager->setLoggedInUser($user);

		$event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
		$event->method('getValue')->willReturn(true);

		$handler = new ConfigureContainerPermissions();
		$result = $handler($event);

		$this->assertFalse($result, 'Regular user should be blocked when limited_groups is set');

		$groups_plugin->setSetting('limited_groups', $original);
		\_elgg_services()->session_manager->removeLoggedInUser();
	}

	public function testContainerPermissionsReturnsNullWhenLimitedGroupsNotSet(): void {
		$groups_plugin = \elgg_get_plugin_from_id('groups');
		if (!$groups_plugin) {
			$this->markTestSkipped('Core groups plugin not active');
		}

		$original = $groups_plugin->getSetting('limited_groups');
		$groups_plugin->setSetting('limited_groups', 'no');

		$user = $this->createUser();
		\_elgg_services()->session_manager->setLoggedInUser($user);

		$event = $this->getMockBuilder(Event::class)->disableOriginalConstructor()->getMock();
		$event->method('getValue')->willReturn(true);

		$handler = new ConfigureContainerPermissions();
		$result = $handler($event);

		$this->assertNull($result, 'Handler should return null when setting is not "yes"');

		$groups_plugin->setSetting('limited_groups', $original);
		\_elgg_services()->session_manager->removeLoggedInUser();
	}
}
