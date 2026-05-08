<?php

namespace hypeJunction\Groups;

use Elgg\IntegrationTestCase;

/**
 * Tests that the Group entity class is mapped correctly for the 'group' subtype.
 * Requires hypegroups to be active (so Bootstrap::setup() registers the class map).
 */
class GroupEntityTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    private function skipIfPluginMissing(): void {
        if (!elgg_get_plugin_from_id('hypegroups') || !elgg_get_plugin_from_id('hypegroups')->isActive()) {
            $this->markTestSkipped('hypegroups not active in test DB');
        }
    }

    public function testGroupEntityClassMappedForGroupSubtype() {
        $this->skipIfPluginMissing();

        $owner = $this->createUser();
        $group = new \ElggGroup();
        $group->name = 'Test Group';
        $group->access_id = ACCESS_PUBLIC;
        $group->owner_guid = $owner->guid;
        $group->container_guid = elgg_get_site_entity()->guid;
        $this->assertTrue((bool) $group->save());

        // Flush cache and reload
        _elgg_services()->entityCache->delete($group->guid);
        $loaded = get_entity($group->guid);

        $this->assertInstanceOf(Group::class, $loaded,
            'Loaded group entity should be mapped to hypeJunction\Groups\Group');

        $group->delete();
    }

    public function testGroupEntityCRUD() {
        $this->skipIfPluginMissing();

        $owner = $this->createUser();

        // Create
        $group = new Group();
        $group->name = 'CRUD Test Group';
        $group->access_id = ACCESS_PUBLIC;
        $group->owner_guid = $owner->guid;
        $group->container_guid = elgg_get_site_entity()->guid;
        $group->custom_setting = 'test_value';
        $saved = $group->save();
        $this->assertTrue((bool) $saved);
        $guid = $group->guid;

        // Read
        _elgg_services()->entityCache->delete($guid);
        $loaded = get_entity($guid);
        $this->assertInstanceOf(Group::class, $loaded);
        $this->assertEquals('CRUD Test Group', $loaded->name);
        $this->assertEquals('test_value', $loaded->custom_setting);

        // Update
        $loaded->name = 'Updated Group Name';
        $this->assertTrue((bool) $loaded->save());
        _elgg_services()->entityCache->delete($guid);
        $updated = get_entity($guid);
        $this->assertEquals('Updated Group Name', $updated->name);

        // Delete
        $this->assertTrue($updated->delete());
        $this->assertFalse((bool) get_entity($guid));
    }

    public function testGroupOwnerCanEdit() {
        $this->skipIfPluginMissing();

        $owner = $this->createUser();
        $other = $this->createUser();

        $group = $this->createGroup(['owner_guid' => $owner->guid]);

        $this->assertTrue($group->canEdit($owner->guid), 'Owner can edit own group');
        $this->assertFalse($group->canEdit($other->guid), 'Non-member non-owner cannot edit group');
    }
}
