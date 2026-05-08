<?php

namespace hypeJunction\Groups;

use Elgg\IntegrationTestCase;

/**
 * Tests that the Group entity class is mapped correctly for the 'group' subtype.
 * Requires hypegroups to be active (so Bootstrap::setup() registers the class map).
 */
class GroupEntityTest extends IntegrationTestCase {

    public function up(): void {}
    public function down(): void {}

    public function getPluginID(): string {
        return 'hypegroups';
    }

    public function testGroupEntityClassMappedForGroupSubtype(): void {
        $group = $this->createGroup();

        _elgg_services()->entityCache->delete($group->guid);
        $loaded = get_entity($group->guid);

        $this->assertInstanceOf(Group::class, $loaded,
            'Loaded group entity should be mapped to hypeJunction\Groups\Group');
    }

    public function testGroupEntityCRUD(): void {
        $group = $this->createGroup(['name' => 'CRUD Test Group']);
        $group->custom_setting = 'test_value';
        $guid = $group->guid;

        _elgg_services()->entityCache->delete($guid);
        $loaded = get_entity($guid);
        $this->assertInstanceOf(Group::class, $loaded);
        $this->assertEquals('CRUD Test Group', $loaded->name);
        $this->assertEquals('test_value', $loaded->custom_setting);

        $loaded->name = 'Updated Group Name';
        elgg_call(ELGG_IGNORE_ACCESS, function() use ($loaded) {
            $loaded->save();
        });
        _elgg_services()->entityCache->delete($guid);
        $updated = get_entity($guid);
        $this->assertEquals('Updated Group Name', $updated->name);

        elgg_call(ELGG_IGNORE_ACCESS, function() use ($updated) {
            $updated->delete();
        });
        $this->assertFalse((bool) get_entity($guid));
    }

    public function testGroupOwnerCanEdit(): void {

        $owner = $this->createUser();
        $other = $this->createUser();

        $group = $this->createGroup(['owner_guid' => $owner->guid]);

        $this->assertTrue($group->canEdit($owner->guid), 'Owner can edit own group');
        $this->assertFalse($group->canEdit($other->guid), 'Non-member non-owner cannot edit group');
    }
}
