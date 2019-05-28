<?php

namespace hypeJunction\Groups;

use Elgg\Hook;

class RegisterCliCommands {

	/**
	 * Register cli commands
	 * @elgg_plugin_hook commands cli
	 *
	 * @param Hook $hook Hook
	 *
	 * @return array
	 */
	public function __invoke(Hook $hook) {
		$commands = $hook->getValue();

		$commands[] = TranslateCommand::class;

		return $commands;
	}
}