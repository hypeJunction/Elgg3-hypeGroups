<?php

namespace hypeJunction\Groups;

use Elgg\Request;
use ElggEntity;
use ElggGroup;
use hypeJunction\Fields\Field;
use Symfony\Component\HttpFoundation\ParameterBag;

class GroupAccessField extends Field {

	/**
	 * {@inheritdoc}
	 */
	public function raw(Request $request, ElggEntity $entity) {
		return [
			'access_id' => $request->getParam('vis'),
			'membership' => $request->getParam('membership'),
			'content_access_mode' => $request->getParam('content_access_mode'),
			'owner_guid' => $request->getParam('owner_guid'),
			'admin_guids' => $request->getParam('admin_guids'),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(ElggEntity $entity, ParameterBag $parameters) {
		/* @var $entity ElggGroup */

		$values = $parameters->get($this->name);

		$svc = elgg()->groups;
		/* @var $svc GroupsService */

		$svc->setGroupAdmins($entity, elgg_extract('admin_guids', $values, []));

		// Group membership - should these be treated with same constants as access permissions?
		$value = elgg_extract('membership', $values);
		if ($entity->membership === null || $value !== null) {
			$is_public_membership = ($value == ACCESS_PUBLIC);
			$entity->membership = $is_public_membership ? ACCESS_PUBLIC : ACCESS_PRIVATE;
		}

		$entity->setContentAccessMode((string) elgg_extract('content_access_mode', $values));

		$user = elgg_get_logged_in_user_entity();
		$value = elgg_extract('owner_guid', $values, $user->guid);

		$owner = get_entity($value);

		if ($owner) {
			$old_owner_guid = $entity->owner_guid;
			$new_owner_guid = ($value === null) ? $old_owner_guid : (int) $value;

			if ($new_owner_guid && $new_owner_guid != $old_owner_guid) {
				// verify new owner is member and old owner/admin is logged in
				if ($entity->isMember(get_user($new_owner_guid)) && ($old_owner_guid == $user->guid || $user->isAdmin())) {
					$entity->owner_guid = $new_owner_guid;

					if ($entity->container_guid == $old_owner_guid) {
						// Even though this action defaults container_guid to the logged in user guid,
						// the group may have initially been created with a custom script that assigned
						// a different container entity. We want to make sure we preserve the original
						// container if it the group is not contained by the original owner.
						$entity->container_guid = $new_owner_guid;
					}
				}
			}

			if ($owner instanceof \ElggUser) {
				$entity->join($owner);
			}
		}

		// Invisible group support
		// is an odd requirement and should be removed. Either the acl creation happens
		// in the action or the visibility moves to a plugin hook
		if (elgg_get_plugin_setting('hidden_groups', 'groups') == 'yes') {
			$value = elgg_extract('vis', $values);
			if ($value !== null) {
				$visibility = (int) $value;

				if ($visibility == ACCESS_PRIVATE) {
					// Make this group visible only to group members. We need to use
					// ACCESS_PRIVATE on the form and convert it to group_acl here
					// because new groups do not have acl until they have been saved once.
					$acl = _groups_get_group_acl($entity);
					if ($acl) {
						$visibility = $acl->id;
					}

					// Force all new group content to be available only to members
					$entity->setContentAccessMode(ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY);
				}

				$entity->access_id = $visibility;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function retrieve(ElggEntity $entity) {
		/* @var $entity ElggGroup */

		$svc = elgg()->groups;
		/* @var $svc GroupsService */

		if (!$entity->guid) {
			return [
				'access_id' => ACCESS_PUBLIC,
				'membership' => ACCESS_PRIVATE,
				'content_access_mode' => ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY,
				'owner_guid' => elgg_get_logged_in_user_guid(),
				'admin_guids' => elgg_get_logged_in_user_guid(),
			];
		}

		return [
			'access_id' => $entity->access_id,
			'membership' => $entity->membership,
			'content_access_mode' => $entity->getContentAccessMode(),
			'owner_guid' => $entity->owner_guid,
			'admin_guids' => $svc->getGroupAdmins($entity),
		];
	}
}
