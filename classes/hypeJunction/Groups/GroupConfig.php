<?php

namespace hypeJunction\Groups;

/**
 * @property bool   $site_menu    Add a link to the site menu
 * @property string $identifier   First URL segment, e.g. groups or courses
 * @property array  $labels       Labels ['en' => ['item' => 'Course', 'collection' => 'Courses']]
 * @property array  $tools        Allowed tools ['blog', 'activity']
 * @property bool   $preset_tools Use preset tools (do not allow owners to change enabled tools)
 * @property array  $parents      Group subtypes that can be used as containers for this group subtype
 * @property bool   $root         Allow at root level
 * @property string $class        Entity class name
 * @property array  $collections  Collection classes
 *                                [
 *                                    'all' => DefaultGroupCollection::class,
 *                                    'owner' => OwnedGroupCollection::class,
 *                                    'member' => JoinedGroupCollection::class,
 *                                 ]
 */
class GroupConfig extends \ArrayObject {

	/**
	 * {@inheritdoc}
	 */
	public function __construct($input = [], $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator") {
		$input = $this->normalize($input);
		parent::__construct($input, $flags, $iterator_class);
	}

	/**
	 * Normalize defaults
	 *
	 * @param array $input Input
	 * @return array
	 */
	protected function normalize(array $input = []) {
		$defaults = [
			'identifier' => 'groups',
			'labels' => [],
			'tools' => null,
			'preset_tools' => false,
			'parents' => [],
			'root' => true,
			'class' => Group::class,
			'collections' => [],
			'site_menu' => true,
		];

		return array_merge($defaults, $input);
	}

}