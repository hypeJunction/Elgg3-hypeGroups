<?php

namespace hypeJunction\Groups;

use PHPUnit\Framework\TestCase;

/**
 * GroupConfig normalizes options with sensible defaults and stores them
 * as array-object properties. Tests run without a database.
 */
class GroupConfigTest extends TestCase {

    public function testDefaultsAppliedWhenNoOptionsGiven() {
        $config = new GroupConfig();

        $this->assertEquals('groups', $config->identifier);
        $this->assertSame([], $config->labels);
        $this->assertNull($config->tools);
        $this->assertFalse($config->preset_tools);
        $this->assertSame([], $config->parents);
        $this->assertTrue($config->root);
        $this->assertEquals(Group::class, $config->class);
        $this->assertSame([], $config->collections);
        $this->assertTrue($config->site_menu);
    }

    public function testSuppliedOptionsOverrideDefaults() {
        $config = new GroupConfig([
            'identifier' => 'teams',
            'labels' => ['en' => ['item' => 'Team']],
            'root' => false,
            'parents' => ['group'],
            'class' => \ElggGroup::class,
        ]);

        $this->assertEquals('teams', $config->identifier);
        $this->assertEquals(['en' => ['item' => 'Team']], $config->labels);
        $this->assertFalse($config->root);
        $this->assertSame(['group'], $config->parents);
        $this->assertEquals(\ElggGroup::class, $config->class);
    }

    public function testUnknownOptionsArePreserved() {
        $config = new GroupConfig(['custom_key' => 'custom_value']);

        $this->assertEquals('custom_value', $config->custom_key);
    }

    public function testConfigImplementsArrayObjectInterface() {
        $config = new GroupConfig(['identifier' => 'clubs']);

        $this->assertInstanceOf(\ArrayObject::class, $config);
        $this->assertEquals('clubs', $config['identifier']);
    }
}
