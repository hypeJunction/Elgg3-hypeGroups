<?php

return [
	'plugin' => [
		'version' => '6.0.0',
	],
	'bootstrap' => \hypeJunction\Groups\Bootstrap::class,
	
	'actions' => [
		'groups/edit' => [
			'controller' => \hypeJunction\Post\SavePostAction::class,
		],
	]
];
