<?php

/**
 * Group edit form
 *
 * This view contains everything related to group access.
 * eg: how can people join this group, who can see the group, etc
 *
 * @package ElggGroups
 */

$entity = elgg_extract('entity', $vars, false);
$membership = elgg_extract('membership', $vars);
$visibility = elgg_extract('vis', $vars);
$owner_guid = elgg_extract('owner_guid', $vars);
$content_access_mode = elgg_extract('content_access_mode', $vars);

$fields = [];

$fields[] = [
	'#type' => 'select',
	'#label' => elgg_echo('groups:membership'),
	'name' => 'membership',
	'id' => 'groups-membership',
	'value' => $membership,
	'options_values' => [
		ACCESS_PRIVATE => elgg_echo('groups:access:private'),
		ACCESS_PUBLIC => elgg_echo('groups:access:public'),
	],
];

if (elgg_get_plugin_setting('hidden_groups', 'groups') == 'yes') {
	$visibility_options = [
		ACCESS_PRIVATE => elgg_echo('groups:access:group'),
		ACCESS_LOGGED_IN => elgg_echo('access:label:logged_in'),
		ACCESS_PUBLIC => elgg_echo('access:label:public'),
	];
	if (elgg_get_config('walled_garden')) {
		unset($visibility_options[ACCESS_PUBLIC]);
	}

	$fields[] = [
		'#type' => 'access',
		'#label' => elgg_echo('groups:visibility'),
		'name' => 'vis',
		'id' => 'groups-vis',
		'value' => $visibility,
		'options_values' => $visibility_options,
		'entity' => $entity,
		'entity_type' => 'group',
		'entity_subtype' => '',
	];
}

$access_mode_params = [
	'#type' => 'select',
	'#label' => elgg_echo('groups:content_access_mode'),
	'name' => 'content_access_mode',
	'id' => 'groups-content-access-mode',
	'value' => $content_access_mode,
	'options_values' => [
		ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED => elgg_echo('groups:content_access_mode:unrestricted'),
		ElggGroup::CONTENT_ACCESS_MODE_MEMBERS_ONLY => elgg_echo('groups:content_access_mode:membersonly'),
	],
];

if ($entity instanceof \ElggGroup) {
	// Disable content_access_mode field for hidden groups because the setting
	// will be forced to members_only regardless of the entered value
	$acl = _groups_get_group_acl($entity);
	if ($acl && ($entity->access_id === $acl->id)) {
		$access_mode_params['disabled'] = 'disabled';
	}

	if ($entity->getContentAccessMode() == ElggGroup::CONTENT_ACCESS_MODE_UNRESTRICTED) {
		// Warn the user that changing the content access mode to more
		// restrictive will not affect the existing group content
		$access_mode_params['#help'] = elgg_echo('groups:content_access_mode:warning');
	}
}

$fields[] = $access_mode_params;

if ($entity && $entity->guid && ($owner_guid == elgg_get_logged_in_user_guid() || elgg_is_admin_logged_in())) {
	$owner_guid_options = [
		'#type' => 'autocomplete',
		'#label' => elgg_echo('groups:owner'),
		'match_on' => 'group_members',
		'name' => 'owner_guid',
		'id' => 'groups-owner-guid',
		'value' =>  $owner_guid,
		'multiple' => false,
		'limit' => 1,
		'options' => [
			'group_guid' => $entity->guid,
		],
	];

	if ($owner_guid == elgg_get_logged_in_user_guid()) {
		$owner_guid_options['#help'] = elgg_echo('groups:owner:warning');
	}

	$fields[] = $owner_guid_options;
}

$fields = elgg_trigger_plugin_hook('fields:access', 'group', $vars, $fields);

foreach ($fields as $field) {
	echo elgg_view_field($field);
}
