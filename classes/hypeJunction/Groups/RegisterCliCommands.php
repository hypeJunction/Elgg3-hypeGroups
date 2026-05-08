<?php

namespace hypeJunction\Groups;

use Elgg\Event;

class RegisterCliCommands {

	/**
	 * Register cli commands
	 * @elgg_plugin_hook commands cli
	 *
	 * @param Event $event Hook
	 *
	 * @return array
	 */
	public function __invoke(Event $event) {
		$commands = $event->getValue();

		$commands[] = TranslateCommand::class;

		return $commands;
	}
}