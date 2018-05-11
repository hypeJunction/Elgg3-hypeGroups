<?php

return [
	'bootstrap' => \hypeJunction\Groups\Bootstrap::class,
	
	'actions' => [
		'groups/edit' => [
			'controller' => \hypeJunction\Post\SavePostAction::class,
		],
	]
];