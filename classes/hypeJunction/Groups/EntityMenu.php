<?php

namespace hypeJunction\Groups;

use Elgg\Hook;

class EntityMenu {

	public function __invoke(Hook $hook) {

		$entity = $hook->getEntityParam();

		if (!$entity instanceof \ElggGroup) {
			return;
		}

		$menu = $hook->getValue();

		if ($entity->canEdit()) {
			$menu[] = \ElggMenuItem::factory([
				'name' => 'edit',
				'text' => elgg_echo('edit'),
				'href' => elgg_generate_url("edit:group:$entity->subtype", [
					'guid' => $entity->guid,
				]),
				'icon' => 'pencil',
			]);

			if (!$entity->isPublicMembership()) {
				$count = elgg_get_entities([
					'type' => 'user',
					'relationship' => 'membership_request',
					'relationship_guid' => $entity->guid,
					'inverse_relationship' => true,
					'count' => true,
				]);

				$text = elgg_echo('groups:membershiprequests');
				$title = $text;
				if ($count) {
					$title = elgg_echo('groups:membershiprequests:pending', [$count]);
				}

				$menu[] = \ElggMenuItem::factory([
					'name' => 'membership_requests',
					'text' => $text,
					'badge' => $count ? $count : null,
					'title' => $title,
					'href' => elgg_generate_url("requests:group:$entity->subtype", [
						'guid' => $entity->guid,
					]),
					'icon' => 'inbox',
				]);
			}
		}

		if ($entity->canDelete()) {
			$menu[] = \ElggMenuItem::factory([
				'name' => 'delete',
				'text' => elgg_echo('delete'),
				'href' => elgg_generate_action_url('entity/delete', ['guid' => $entity->guid]),
				'confirm' => true,
				'icon' => 'trash',
				'link_class' => 'elgg-state elgg-state-danger',
				'priority' => 900,
			]);
		}

		$user = elgg_get_logged_in_user_entity();

		if ($entity->isMember($user)) {
			$leave = groups_get_group_leave_menu_item($entity, $user);
			if ($leave) {
				$leave->addLinkClass('elgg-state elgg-state-danger');
				$leave->setPriority(900);
				$menu[] = $leave;
			}
		}

		return $menu;
	}
}