<?php

namespace hypeJunction\Groups;

use Elgg\Hook;
use Elgg\Request;
use ElggEntity;
use ElggGroup;
use hypeJunction\Fields\Collection;
use hypeJunction\Fields\CoverField;
use hypeJunction\Fields\HiddenField;
use hypeJunction\Fields\IconField;
use hypeJunction\Fields\MetaField;
use hypeJunction\Fields\TitleField;
use hypeJunction\ValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SetGroupFields {

	/**
	 * Setup group fields
	 *
	 * @elgg_plugin_hook fields group
	 *
	 * @param Hook $hook Hook
	 *
	 * @return Collection
	 * @throws \InvalidParameterException
	 */
	public function __invoke(Hook $hook) {

		$entity = $hook->getEntityParam();

		if (!$entity instanceof ElggGroup) {
			return null;
		}

		$fields = $hook->getValue();
		/* @var $fields Collection */

		$fields->add('name', new TitleField([
			'type' => 'text',
			'maxlength' => 50,
			'is_profile_field' => false,
			'required' => true,
			'priority' => 10,
		]));

		$config = (array) elgg_get_config('group');

		foreach ($config as $prop => $type) {
			$fields->add($prop, new MetaField([
				'type' => $type,
				'is_profile_field' => true,
				'priority' => 200,
			]));
		}

		$fields->add('access', new GroupAccessField([
			'type' => 'groups/access',
			'section' => 'sidebar',
			'priority' => 50,
			'is_profile_field' => false,
			'required' => true,
			'#help' => false,
		]));

		$fields->add('tools', new GroupToolsField([
			'type' => 'groups/tools',
			'section' => 'sidebar',
			'priority' => 100,
			'is_profile_field' => false,
			'required' => true,
		]));

		$fields->add('icon', new IconField([
			'type' => 'file',
			'section' => 'sidebar',
			'priority' => 400,
			'is_profile_field' => false,
		]));

		$fields->add('cover', new CoverField([
			'type' => 'post/cover',
			'section' => 'sidebar',
			'priority' => 400,
			'is_profile_field' => false,
		]));

		// In case other plugins are listening to action
		$fields->add('group_guid', new GroupGuidField([
			'type' => 'hidden',
			'is_profile_field' => false,
		]));

		return $fields;
	}
}