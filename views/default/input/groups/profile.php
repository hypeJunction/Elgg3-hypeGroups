<?php

$value = (array) elgg_extract('value', $vars, []);
unset($vars['profile']);

echo elgg_view('groups/edit/profile', array_merge($vars, $value));