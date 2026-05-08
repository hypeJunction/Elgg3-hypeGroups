<?php

namespace hypeJunction\Groups;

use Elgg\IntegrationTestCase;

/**
 * Verifies that Bootstrap::init() registers the expected hook handlers
 * and that Bootstrap::boot() wires the GroupsService into the DI container.
 */
class BootstrapTest extends IntegrationTestCase {

    public function up() {}
    public function down() {}

    public function getPluginID(): string {
        return '';
    }

    private function skipIfPluginMissing(): void {
        if (!elgg_get_plugin_from_id('hypegroups')) {
            $this->markTestSkipped('hypegroups not installed in test DB');
        }
    }

    public function testGroupsServiceRegisteredInContainer() {
        $this->skipIfPluginMissing();
        $this->assertTrue(\Elgg\Application::$_instance !== null);
        $svc = elgg()->groups;
        $this->assertInstanceOf(GroupsService::class, $svc);
    }

    public function testEditPermissionsHookRegistered() {
        $this->skipIfPluginMissing();
        $handlers = _elgg_services()->hooks->getAllHandlers();
        $this->assertArrayHasKey('permissions_check', $handlers);
        $this->assertArrayHasKey('group', $handlers['permissions_check']);
    }

    public function testContainerPermissionsHookRegistered() {
        $this->skipIfPluginMissing();
        $handlers = _elgg_services()->hooks->getAllHandlers();
        $this->assertArrayHasKey('container_permissions_check', $handlers);
        $this->assertArrayHasKey('group', $handlers['container_permissions_check']);
    }

    public function testGroupFieldsHookRegistered() {
        $this->skipIfPluginMissing();
        $handlers = _elgg_services()->hooks->getAllHandlers();
        $this->assertArrayHasKey('fields', $handlers);
        $this->assertArrayHasKey('group', $handlers['fields']);
    }

    public function testToolOptionsHookRegistered() {
        $this->skipIfPluginMissing();
        $handlers = _elgg_services()->hooks->getAllHandlers();
        $this->assertArrayHasKey('tool_options', $handlers);
        $this->assertArrayHasKey('group', $handlers['tool_options']);
    }
}
