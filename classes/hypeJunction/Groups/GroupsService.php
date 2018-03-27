<?php

namespace hypeJunction\Groups;

use Elgg\Database\QueryBuilder;
use Elgg\Hook;
use Elgg\Values;

class GroupsService {

	/**
	 * @var GroupConfig[]
	 */
	protected $subtypes;

	/**
	 * Get subtype config
	 *
	 * @param string $subtype
	 *
	 * @return GroupConfig|null
	 */
	public function __get($subtype) {
		return elgg_extract($subtype, $this->subtypes);
	}

	/**
	 * Register a new group subtype
	 *
	 * @param string            $subtype Group subtype, e.g. course
	 * @param array|GroupConfig $options Subtype options
	 *
	 * @option bool   $site_menu    Add a link to the site menu
	 * @option string $identifier   First URL segment, e.g. groups or courses
	 * @option array  $labels       Labels ['en' => ['item' => 'Course', 'collection' => 'Courses']]
	 * @option array  $tools        Allowed tools ['blog', 'activity']
	 * @option bool   $preset_tools Use preset tools (do not allow owners to change enabled tools)
	 * @option array  $parents      Group subtypes that can be used as containers for this group subtype
	 * @option bool   $root         Allow at root level
	 * @option string $class        Entity class name
	 * @option array  $collections  Collection classes
	 *                              [
	 *                                'all' => DefaultGroupCollection::class,
	 *                                'owner' => OwnedGroupCollection::class,
	 *                                'member' => JoinedGroupCollection::class,
	 *                              ]
	 * @return void
	 */
	public function registerSubtype($subtype, $options = []) {
		if (is_array($options)) {
			$options = new GroupConfig($options);
		}

		$this->subtypes[$subtype] = $options;
	}

	/**
	 * Unregister subtype
	 *
	 * @param string $subtype Subtype
	 *
	 * @return void
	 */
	public function unregisterSubtype($subtype) {
		unset($this->subtypes[$subtype]);
	}

	/**
	 * Get subtypes registered for a specific identifier
	 *
	 * @param string $identifier Page identifier
	 *
	 * @return array
	 */
	public function getSubtypes($identifier = null) {
		if (!$identifier) {
			return array_keys($this->subtypes);
		}

		$subtypes = [];

		foreach ($this->subtypes as $subtype => $options) {
			if ($options->identifier == $identifier) {
				$subtypes[] = $subtype;
			}
		}

		return $subtypes;
	}

	/**
	 * Get config
	 * @return GroupConfig[]
	 */
	public function all() {
		return $this->subtypes;
	}

	/**
	 * Setup group subtypes
	 *
	 * @throws \InvalidParameterException
	 */
	public function setup() {

		$this->cleanup();

		foreach ($this->subtypes as $subtype => $options) {
			$class = $options->class;
			elgg_set_entity_class('group', $subtype, $class);
			elgg_register_entity_type('group', $subtype);

			elgg_register_plugin_hook_handler('uses:comments', "group:$subtype", [\Elgg\Values::class, 'getFalse']);
			elgg_register_plugin_hook_handler('uses:autosave', "group:$subtype", [\Elgg\Values::class, 'getFalse']);
			elgg_register_plugin_hook_handler('uses:location', "group:$subtype", [\Elgg\Values::class, 'getTrue']);

			$identifier = $options->identifier;

			$routes = [
				"collection:group:{$subtype}:all" => [
					'path' => "/{$identifier}/all",
					'resource' => 'groups/all',
				],
				"collection:group:{$subtype}:owner" => [
					'path' => "/{$identifier}/owner/{username}",
					'resource' => 'groups/owner',
				],
				"collection:group:{$subtype}:member" => [
					'path' => "/{$identifier}/member/{username}",
					'resource' => 'groups/member',
				],
				"collection:group:{$subtype}:invitations" => [
					'path' => "/{$identifier}/invitations/{username}",
					'resource' => 'groups/invitations',
				],
				"collection:group:{$subtype}:search" => [
					'path' => "/{$identifier}/search",
					'resource' => 'groups/search',
				],
				"add:group:{$subtype}" => [
					'path' => "/{$identifier}/add/{container_guid}",
					'resource' => 'groups/add',
					'middleware' => [
						\Elgg\Router\Middleware\Gatekeeper::class,
					],
					'defaults' => [
						'subtype' => $subtype,
					],
				],
				"view:group:{$subtype}" => [
					'path' => "/{$identifier}/profile/{guid}/{title?}",
					'resource' => 'groups/profile',
				],
				"edit:group:{$subtype}" => [
					'path' => "/{$identifier}/edit/{guid}",
					'resource' => 'groups/edit',
					'middleware' => [
						\Elgg\Router\Middleware\Gatekeeper::class,
					],
				],
				"invite:group:{$subtype}" => [
					'path' => "/{$identifier}/invite/{guid}",
					'resource' => 'groups/invite',
					'middleware' => [
						\Elgg\Router\Middleware\Gatekeeper::class,
					],
				],
				"requests:group:{$subtype}" => [
					'path' => "/{$identifier}/requests/{guid}",
					'resource' => 'groups/requests',
					'middleware' => [
						\Elgg\Router\Middleware\Gatekeeper::class,
					],
				],
				"collection:group:{$subtype}:featured" => [
					'path' => "/{$identifier}/featured",
					'resource' => 'groups/featured',
				],
			];

			foreach ($routes as $route_name => $route_options) {
				elgg_register_route($route_name, $route_options);
			}

			$collections = (array) $options->collections;

			elgg_register_collection(
				"collection:group:{$subtype}:all",
				elgg_extract('all', $collections, \hypeJunction\Groups\DefaultGroupCollection::class)
			);

			elgg_register_collection(
				"collection:group:{$subtype}:owner",
				elgg_extract('owner', $collections, \hypeJunction\Groups\OwnedGroupCollection::class)
			);

			elgg_register_collection(
				"collection:group:{$subtype}:member",
				elgg_extract('member', $collections, \hypeJunction\Groups\JoinedGroupCollection::class)
			);

			elgg_register_collection(
				"collection:group:{$subtype}:featured",
				elgg_extract('featured', $collections, \hypeJunction\Groups\FeaturedGroupCollection::class)
			);

			$labels = (array) $options->labels;

			foreach ($labels as $lang => $lang_labels) {
				$singular = elgg_extract('item', $lang_labels);
				$plural = elgg_extract('collection', $lang_labels);

				add_translation($lang, [
					"item:group:$subtype" => $singular,
					"collection:group:$subtype" => $plural,
				]);
			}

			if ($options->site_menu) {
				elgg_register_menu_item('site', [
					'name' => $identifier,
					'href' => "$identifier/all",
					'text' => elgg_echo("collection:group:$subtype"),
				]);
			}

			elgg_register_plugin_hook_handler('container_logic_check', 'group', function (Hook $hook) use ($subtype, $options) {
				$parents = (array) $options->parents;
				$root = $options->root;

				$container = $hook->getParam('container');
				$writing_subtype = $hook->getParam('subtype');

				if ($writing_subtype !== $subtype) {
					return null;
				}

				$value = $hook->getValue();
				if (!isset($value)) {
					$value = true;
				}

				if ($container instanceof \ElggGroup) {
					return $value && in_array($container->getSubtype(), $parents);
				}

				return $value && $root;
			});

		}
	}

	/**
	 * Clean up group plugin registrations
	 * @return void
	 */
	protected function cleanup() {
		elgg_unregister_plugin_hook_handler('entity:url', 'group', 'groups_set_url');

		elgg_unregister_menu_item('site', 'groups');

		$routes = [
			'default:group:group',
			'collection:group:group:all',
			'collection:group:group:owner',
			'collection:group:group:member',
			'collection:group:group:invitations',
			'collection:group:group:search',
			'add:group:group',
			'view:group:group',
			'edit:group:group',
			'invite:group:group',
			'requests:group:group',
		];

		foreach ($routes as $route) {
			elgg_unregister_route($route);
		}
	}

	/**
	 * Sniff page identifier from page URL
	 *
	 * @return string
	 */
	public function getPageIdentifier() {
		$url = current_page_url();
		$path = substr($url, strlen(elgg_get_site_url()));

		$parts = explode('/', $path);

		return array_shift($parts) ? : '';
	}

	/**
	 * Get groups user is member of
	 *
	 * @param \ElggUser $user    User
	 * @param array     $options ege* options
	 *
	 * @return \ElggEntity[]|int|mixed
	 */
	public function getJoinedGroups(\ElggUser $user, array $options = []) {
		$defaults = [
			'types' => 'group',
			'relationship' => 'member',
			'relationship_guid' => $user->guid,
			'inverse_relationship' => false,
			'limit' => 0,
		];

		$options = array_merge($defaults, $options);

		return elgg_get_entities($options);
	}

	/**
	 * Get groups user owns or administers
	 *
	 * @param \ElggUser $user    User
	 * @param array     $options ege* options
	 *
	 * @return \ElggEntity[]|int|mixed
	 */
	public function getAdministeredGroups(\ElggUser $user, array $options = []) {
		$defaults = [
			'types' => 'group',
			'limit' => 0,
		];

		$options = array_merge($defaults, $options);

		$filter = function (QueryBuilder $qb, $from_alias = 'e') use ($user) {
			$sub = $qb->subquery('entity_relationships');
			$sub->select(1)
				->where($qb->compare('guid_one', '=', $user, ELGG_VALUE_GUID))
				->andWhere($qb->compare('relationship', '=', 'group_admin', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_two', '=', "$from_alias.guid"));

			return $qb->merge([
				$qb->compare("$from_alias.owner_guid", '=', $user, ELGG_VALUE_GUID),
				"EXISTS ({$sub->getSQL()})"
			], 'OR');
		};

		$options['wheres'][] = $filter;

		return elgg_get_entities($options);
	}

	/**
	 * Get members of a group
	 *
	 * @param \ElggGroup $group   Group
	 * @param array      $options ege* options
	 *
	 * @return \ElggEntity[]|int|mixed
	 */
	public function getGroupMembers(\ElggGroup $group, array $options = []) {
		$defaults = [
			'types' => 'user',
			'relationship' => 'member',
			'relationship_guid' => $group->guid,
			'inverse_relationship' => true,
			'limit' => 0,
		];

		$options = array_merge($defaults, $options);

		return elgg_get_entities($options);
	}

	/**
	 * Get administrators of a group
	 *
	 * @param \ElggGroup $group   Group
	 * @param array      $options ege* options
	 *
	 * @return \ElggEntity[]|int|mixed
	 */
	public function getGroupAdmins(\ElggGroup $group, array $options = []) {
		$defaults = [
			'types' => 'user',
			'limit' => 0,
		];

		$options = array_merge($defaults, $options);

		$filter = function (QueryBuilder $qb, $from_alias = 'e') use ($group) {
			$sub = $qb->subquery('entity_relationships');
			$sub->select(1)
				->where($qb->compare('guid_one', '=', "$from_alias.guid"))
				->andWhere($qb->compare('relationship', '=', 'group_admin', ELGG_VALUE_STRING))
				->andWhere($qb->compare('guid_two', '=', $group, ELGG_VALUE_GUID));

			return $qb->merge([
				$qb->compare("$from_alias.guid", '=', $group->owner_guid),
				"EXISTS ({$sub->getSQL()})"
			], 'OR');
		};

		$options['wheres'][] = $filter;

		return elgg_get_entities($options);
	}

	/**
	 * Replace group admins
	 *
	 * @param \ElggGroup $group  Group
	 * @param array      $admins Admin guids or users
	 *
	 * @return void
	 * @throws \DataFormatException
	 */
	public function setGroupAdmins(\ElggGroup $group, array $admins = []) {
		$current_admins = $this->getGroupAdmins($group, [
			'callback' => function($e) {
				return (int) $e->guid;
			}
		]);

		$current_admins = (array) $current_admins;
		$admins = (array) Values::normalizeGuids($admins);

		$remove = array_diff($current_admins, $admins);
		$add = array_diff($admins, $current_admins);

		foreach ($remove as $guid) {
			remove_entity_relationship($guid, 'group_admin', $group->guid);
		}

		foreach ($add as $guid) {
			add_entity_relationship($guid, 'group_admin', $group->guid);
		}
	}
}