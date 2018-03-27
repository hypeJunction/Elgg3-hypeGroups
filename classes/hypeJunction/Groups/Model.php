<?php

namespace hypeJunction\Groups;

use Elgg\Hook;
use Elgg\Request;
use ElggEntity;
use ElggGroup;
use hypeJunction\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Model {

	/**
	 * Setup group fields
	 *
	 * @param Hook $hook Hook
	 *
	 * @return array|null
	 */
	public function __invoke(Hook $hook) {

		$entity = $hook->getEntityParam();

		if (!$entity instanceof ElggGroup) {
			return null;
		}

		$tools = elgg_get_group_tool_options($entity);

		$fields = [];

		$fields['name'] = [
			'#type' => 'text',
			'#section' => 'content',
			'#input' => function (Request $request) {
				return elgg_get_title_input('name');
			},
			'#profile' => false,
			'required' => true,
			'#priority' => 100,
		];

		$config = (array) elgg_get_config('group');

		foreach ($config as $prop => $type) {
			$fields[$prop] = [
				'#type' => $type,
				'#section' => 'content',
				'#profile' => true,
				'#priority' => 200,
			];
		}

		$fields['access'] = [
			'#type' => 'groups/access',
			'#input' => function (Request $request) {
				return [
					'access_id' => $request->getParam('vis'),
					'membership' => $request->getParam('membership'),
					'content_access_mode' => $request->getParam('content_access_mode'),
					'owner_guid' => $request->getParam('owner_guid'),
					'admin_guids' => $request->getParam('admin_guids'),
				];
			},
			'#setter' => function (ElggEntity $entity, $values) {
				/* @var $entity ElggGroup */

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
			},
			'#getter' => function (ElggEntity $entity) {
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
			},
			'#section' => 'sidebar',
			'#priority' => 50,
			'#profile' => false,
			'required' => true,
			'#help' => false,
		];

		$fields['tools'] = [
			'#type' => 'groups/tools',
			'#visibility' => function(\ElggEntity $entity) {
				$svc = elgg()->groups;
				/* @var $svc \hypeJunction\Groups\GroupsService */

				$subtype = $entity->getSubtype();
				$config = $svc->$subtype;

				if ($config && $config->preset_tools) {
					return false;
				}
			},
			'#input' => function (Request $request) use ($tools) {
				$values = [];

				foreach ($tools as $tool) {
					$value = $request->getParam("{$tool->name}_enable");
					if (isset($value)) {
						$values[$tool->name] = $value === 'yes' ? true : false;
					} else {
						$values[$tool->name] = $value === 'yes' ? true : false;
					}
				}

				return $values;
			},
			'#setter' => function (ElggEntity $entity, $value) {
				/* @var $entity ElggGroup */
				foreach ($value as $name => $enabled) {
					$enabled ? $entity->enableTool($name) : $entity->disableTool($name);
				}
			},
			'#getter' => function (ElggEntity $entity) use ($tools) {
				/* @var $entity ElggGroup */
				$value = [];

				foreach ($tools as $tool) {
					$value["{$tool->name}_enable"] = $entity->isToolEnabled($tool->name) ? 'yes' : 'no';
				}

				return $value;
			},
			'#section' => 'sidebar',
			'#priority' => 100,
			'#profile' => false,
			'required' => true,
		];

		$fields['icon'] = [
			'#type' => 'file',
			'#section' => 'sidebar',
			'name' => 'icon',
			'#input' => function (Request $request) {
				$files = elgg_get_uploaded_files('icon');
				if (empty($files)) {
					return null;
				}

				return array_shift($files);
			},
			'#validate' => function ($value, $field) {
				$required = elgg_extract('required', $field);
				$label = elgg_extract('#label', $field);

				if ($required) {
					if ((!$value instanceof UploadedFile)) {
						throw new ValidationException(elgg_echo('error:field:required', [$label]));
					}

					if (!$value->isValid()) {
						throw new ValidationException(elgg_echo('error:field:invalid_file', [
							$label,
							elgg_get_friendly_upload_error($value->getError()),
						]));
					}
				}
			},
			'#getter' => function (ElggEntity $entity) {
				$icon = $entity->getIcon('master');

				return $icon->exists() ? $icon : null;
			},
			'#setter' => function (ElggEntity $entity) {
				return $entity->saveIconFromUploadedFile('icon');
			},
			'#priority' => 400,
			'#profile' => false,
			'#visibility' => function (ElggEntity $entity) {
				$params = [
					'entity' => $entity,
				];

				return elgg()->hooks->trigger(
					'uses:icon',
					"$entity->type:$entity->subtype",
					$params,
					true
				);
			},
		];

		$fields['cover'] = [
			'#type' => 'post/cover',
			'#section' => 'sidebar',
			'#input' => function (Request $request) {
				$files = elgg_get_uploaded_files('cover');
				$cover = $request->getParam('cover', []);

				return [
					'file' => elgg_extract('file', $files),
					'url' => elgg_extract('url', $cover),
				];
			},
			'#validate' => function ($value, $params) {
				$required = elgg_extract('required', $params);
				$label = elgg_extract('#label', $params);

				if ($required) {
					if ((!$value['file'] instanceof UploadedFile) && empty($value['url'])) {
						throw new ValidationException(elgg_echo('error:field:required', [$label]));
					}
				}
			},
			'#getter' => function (ElggEntity $entity) {
				$svc = elgg()->{'posts.post'};
				/* @var $svc \hypeJunction\Post\Post */

				$cover = $svc->getCover($entity);

				if ($cover->getCoverUrl()) {
					return $cover;
				}
			},
			'#setter' => function (ElggEntity $entity, $value) {
				$file = elgg_extract('file', $value);
				$url = elgg_extract('url', $value);

				if ($file instanceof UploadedFile && $file->isValid()) {
					$tmp_filename = time() . $file->getClientOriginalName();
					$tmp = new \ElggFile();
					$tmp->owner_guid = $entity->guid;
					$tmp->setFilename("tmp/$tmp_filename");
					$tmp->open('write');
					$tmp->close();

					copy($file->getPathname(), $tmp->getFilenameOnFilestore());

					$entity->saveIconFromElggFile($tmp, 'cover');

					$tmp->delete();
				} else if ($url) {
					$bytes = file_get_contents($url);

					if (!empty($bytes)) {
						$tmp = new \ElggFile();
						$tmp->owner_guid = $entity->guid;
						$tmp->setFilename("tmp/" . pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_BASENAME));

						$tmp->open('write');
						$tmp->write($bytes);
						$tmp->close();

						$entity->saveIconFromElggFile($tmp, 'cover');

						$tmp->delete();
					}
				}
			},
			'#priority' => 400,
			'#profile' => false,
			'#visibility' => function (ElggEntity $entity) {
				$params = [
					'entity' => $entity,
				];

				return elgg()->hooks->trigger(
					'uses:cover',
					"$entity->type:$entity->subtype",
					$params,
					true
				);
			},
		];

		// In case other plugins are listening to action
		$fields['group_guid'] = [
			'#type' => 'hidden',
			'#getter' => function (ElggEntity $entity) {
				return $entity->guid;
			},
			'#setter' => false,
			'#profile' => false,
		];

		return $fields;
	}
}