<?php

$mod = elgg_view_form('admin/groups/subtypes/add');
echo elgg_view_module('info', elgg_echo('admin:groups:subtypes:add'), $mod, array(
	'groups-subtypes-config-module',
));

echo elgg_view_form('admin/groups/subtypes/config');

echo elgg_view_form('admin/groups/subtypes/change_subtype');