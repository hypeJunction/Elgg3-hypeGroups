<?php

namespace hypeJunction\Groups;

use Elgg\IntegrationTestCase;

/**
 * Tests GroupsService subtype registration, lookup, and removal.
 * Uses a fresh GroupsService instance to avoid modifying global state.
 */
class GroupsServiceTest extends IntegrationTestCase {

    /** @var GroupsService */
    private $svc;

    public function up() {
        $this->svc = new GroupsService();
    }

    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    public function testRegisterSubtypeStoresConfig() {
        $this->svc->registerSubtype('team', [
            'identifier' => 'teams',
            'labels' => ['en' => ['item' => 'Team', 'collection' => 'Teams']],
        ]);

        $all = $this->svc->all();
        $this->assertArrayHasKey('team', $all);
        $this->assertInstanceOf(GroupConfig::class, $all['team']);
    }

    public function testRegisterSubtypeAcceptsGroupConfigObject() {
        $config = new GroupConfig(['identifier' => 'clubs']);
        $this->svc->registerSubtype('club', $config);

        $all = $this->svc->all();
        $this->assertSame($config, $all['club']);
    }

    public function testGetSubtypesReturnsAllSubtypeKeys() {
        $this->svc->registerSubtype('group', ['identifier' => 'groups']);
        $this->svc->registerSubtype('team', ['identifier' => 'teams']);

        $subtypes = $this->svc->getSubtypes();
        $this->assertContains('group', $subtypes);
        $this->assertContains('team', $subtypes);
    }

    public function testGetSubtypesByIdentifierFiltersCorrectly() {
        $this->svc->registerSubtype('group', ['identifier' => 'groups']);
        $this->svc->registerSubtype('team', ['identifier' => 'teams']);

        $groups = $this->svc->getSubtypes('groups');
        $this->assertContains('group', $groups);
        $this->assertNotContains('team', $groups);

        $teams = $this->svc->getSubtypes('teams');
        $this->assertContains('team', $teams);
        $this->assertNotContains('group', $teams);
    }

    public function testGetSubtypesByIdentifierReturnsEmptyForUnknownIdentifier() {
        $this->svc->registerSubtype('group', ['identifier' => 'groups']);

        $result = $this->svc->getSubtypes('nonexistent');
        $this->assertSame([], $result);
    }

    public function testUnregisterSubtypeRemovesEntry() {
        $this->svc->registerSubtype('group', ['identifier' => 'groups']);
        $this->svc->registerSubtype('team', ['identifier' => 'teams']);

        $this->svc->unregisterSubtype('team');

        $subtypes = $this->svc->getSubtypes();
        $this->assertNotContains('team', $subtypes);
        $this->assertContains('group', $subtypes);
    }

    public function testMagicGetReturnsConfigForRegisteredSubtype() {
        $this->svc->registerSubtype('group', ['identifier' => 'groups']);

        $config = $this->svc->group;
        $this->assertInstanceOf(GroupConfig::class, $config);
        $this->assertEquals('groups', $config->identifier);
    }

    public function testMagicGetReturnsNullForUnknownSubtype() {
        $result = $this->svc->nonexistent;
        $this->assertNull($result);
    }

    public function testAllReturnsEmptyArrayWhenNoSubtypesRegistered() {
        $this->assertSame([], $this->svc->all());
    }
}
