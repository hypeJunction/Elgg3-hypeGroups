<?php

$value = (array) elgg_extract('value', $vars, []);
unset($vars['value']);

echo elgg_view('groups/edit/tools', array_merge($vars, $value));
