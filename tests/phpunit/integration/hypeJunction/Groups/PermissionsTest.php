<?php

namespace hypeJunction\Groups;

use Elgg\IntegrationTestCase;

/**
 * Tests ConfigureEditPermissions (group admin can edit) and
 * ConfigureContainerPermissions (limited_groups setting).
 */
class PermissionsTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    // --- ConfigureEditPermissions ---

    public function testGroupAdminCanEditGroup() {
        $owner = $this->createUser();
        $member = $this->createUser();
        $group = $this->createGroup(['owner_guid' => $owner->guid]);

        // Make $member a group admin
        add_entity_relationship($member->guid, 'group_admin', $group->guid);

        $hook = $this->getMockBuilder(\Elgg\Hook::class)->getMock();
        $hook->method('getEntityParam')->willReturn($group);
        $hook->method('getUserParam')->willReturn($member);
        $hook->method('getValue')->willReturn(false);

        $handler = new ConfigureEditPermissions();
        $result = $handler($hook);

        $this->assertTrue($result, 'Group admin should receive edit permission');
    }

    public function testNonAdminGroupMemberCannotEditViaAdminHook() {
        $owner = $this->createUser();
        $member = $this->createUser();
        $group = $this->createGroup(['owner_guid' => $owner->guid]);

        // $member is NOT a group_admin
        $hook = $this->getMockBuilder(\Elgg\Hook::class)->getMock();
        $hook->method('getEntityParam')->willReturn($group);
        $hook->method('getUserParam')->willReturn($member);
        $hook->method('getValue')->willReturn(false);

        $handler = new ConfigureEditPermissions();
        $result = $handler($hook);

        $this->assertNull($result, 'Non-admin member should not receive edit permission from this hook');
    }

    public function testEditPermissionsHookIgnoresNonGroupEntities() {
        $user = $this->createUser();
        $object = $this->createObject(['subtype' => 'blog', 'owner_guid' => $user->guid]);

        $hook = $this->getMockBuilder(\Elgg\Hook::class)->getMock();
        $hook->method('getEntityParam')->willReturn($object);
        $hook->method('getUserParam')->willReturn($user);
        $hook->method('getValue')->willReturn(false);

        $handler = new ConfigureEditPermissions();
        $result = $handler($hook);

        $this->assertNull($result, 'Handler should return null for non-group entities');
    }

    // --- ConfigureContainerPermissions ---

    public function testContainerPermissionsReturnsFalseWhenLimitedGroupsEnabledForRegularUser() {
        // Set limited_groups = 'yes' on the core groups plugin
        $groups_plugin = \elgg_get_plugin_from_id('groups');
        if (!$groups_plugin) {
            $this->markTestSkipped('Core groups plugin not active');
        }

        $original = $groups_plugin->getSetting('limited_groups');
        $groups_plugin->setSetting('limited_groups', 'yes');

        $user = $this->createUser();
        \elgg_get_session()->setLoggedInUser($user);

        $hook = $this->getMockBuilder(\Elgg\Hook::class)->getMock();
        $hook->method('getValue')->willReturn(true);

        $handler = new ConfigureContainerPermissions();
        $result = $handler($hook);

        $this->assertFalse($result, 'Regular user should be blocked when limited_groups is set');

        // Restore
        $groups_plugin->setSetting('limited_groups', $original);
        \elgg_get_session()->removeLoggedInUser();
    }

    public function testContainerPermissionsReturnsNullWhenLimitedGroupsNotSet() {
        $groups_plugin = \elgg_get_plugin_from_id('groups');
        if (!$groups_plugin) {
            $this->markTestSkipped('Core groups plugin not active');
        }

        $original = $groups_plugin->getSetting('limited_groups');
        $groups_plugin->setSetting('limited_groups', 'no');

        $user = $this->createUser();
        \elgg_get_session()->setLoggedInUser($user);

        $hook = $this->getMockBuilder(\Elgg\Hook::class)->getMock();
        $hook->method('getValue')->willReturn(true);

        $handler = new ConfigureContainerPermissions();
        $result = $handler($hook);

        $this->assertNull($result, 'Handler should return null when setting is not "yes"');

        $groups_plugin->setSetting('limited_groups', $original);
        \elgg_get_session()->removeLoggedInUser();
    }
}
