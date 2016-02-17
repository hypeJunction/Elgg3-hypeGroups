<?php

$subtype = strtolower(get_input('subtype', ''));

if ($subtype && add_subtype('group', $subtype)) {
	system_message(elgg_echo('admin:groups:subtypes:add:success'));
} else {
	register_error(elgg_echo('admin:groups:subtypes:add:error'));
}