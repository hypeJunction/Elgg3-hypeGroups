hypeGroups
==========
![Elgg 3.0](https://img.shields.io/badge/Elgg-3.0-orange.svg?style=flat-square)

## Features

* Extended search and sort functionality
* API to add new group subtypes
* API to manage group hierarchies
* API for managing group fields
* API for restricting group tools, as well as using preset tools

## Notes

### Subtypes

Registering new subtypes and configuring them is made easy.

Here is an example of how to remove groups from the top level of the site, and making them subgroups of a new subtype called classroom.

```php

		$svc = elgg()->groups;
		/* @var $svc \hypeJunction\Groups\GroupsService */

		$svc->registerSubtype('classroom', [
			'labels' => [
				'en' => [
					'item' => 'Classroom',
					'collection' => 'Classrooms',
				],
			],
			'root' => true,
			'identifier' => 'classrooms',
			'class' => \CustomPlugin\Classroom::class,
			'collections' => [
				'all' => \CustomPlugin\DefaultClassroomCollection::class,
				'owner' => \CustomPlugin\OwnedClassroomCollection::class,
				'member' => \CustomPlugin\JoinedClassroomCollection::class,
			],
		]);

		$svc->registerSubtype('group', [
			'site_menu' => false,
			'labels' => [
				'en' => [
					'item' => 'Group',
					'collection' => 'Groups',
				],
			],
			'root' => false,
			'parents' => ['classroom'],
			'identifier' => 'groups',
		]);

```

You can put multiple subtypes into a collection by assigning them to the same `identifier`, e.g. you could create `usa_state` and `canada_province` subtypes and register them for `regions` identifier.

### Fields

Fields are managed by hypePost. Please see the documentation there for more information.