Group Subtypes for Elgg
=======================
![Elgg 2.0](https://img.shields.io/badge/Elgg-2.0.x-orange.svg?style=flat-square)

## Features

 * API for introducing new group subtypes
 * A tool for upgrading existing groups to one of the introduced subtypes
 * Admin interface for configuring root level and subgroup subtypes
 * Admin interface for creating groups with fixed tool presets

![Group Subtypes](https://raw.github.com/hypeJunction/Elgg-group_subtypes/master/screenshots/group_subtypes.png "Admin Interface for managing group subtypes")

## Description

The plugin provides high level hooks and some preliminary UI for introducing group subtypes,
spreading out groups across multiple page handlers, using context specific language strings etc.
The plugin does not provide actual translations, and does not change existing resource views -
those are done in sister plugins (group_lists, group_membership, group_profiles).

Please note that the plugin overwrites some of the group views, which are commonly
overwritten by other plugins (e.g. group_tools), so you may need to integrate them if you rely
on both functionalities.

## Usage

### Hooks

 * `'list_subtypes',$identifier` - filters group subtypes to be displayed on an `$identifier` page
 * `'page_identifier',"$type:$subtype"` - filter the route page identifier for a group of given type and subtype
 * `'permissions_check:parent','group'` - filter the permission for a parent entity to have the group as a child

### Translations

Once you set up the subtypes, there will be numerous untranslated language keys. I am yet to write a plugin to automate the process.
In the meantime, you can do the following:

1. Create a new plugin for your translations
2. Add your language files to `/languages/`, e.g. `/languages/en.php`
3. Add translations 2 translations for each subtype, e.e. if your subtype is school:

```php
// /languages/en.php
return [
	'group:school' => 'School',
	'item:group:school' => 'Schools',
];
```

4. In your `start.php`, add the following:

```php
elgg_register_event_handler('ready', 'system', 'group_subtypes_register_translations');

/**
 * Use groups plugin translations for group subtypes
 * @global type $GLOBALS
 */
function group_subtypes_register_translations() {

	global $GLOBALS;

	$conf = group_subtypes_get_config();
	$identifiers = array();
	foreach ($conf as $subtype => $options) {
		$identifier = elgg_extract('identifier', $options);
		if ($identifier && $identifier !== 'groups') {
			$identifiers[$subtype] = $identifier;
		}
	}

	$languages = array_keys(get_installed_translations());

	foreach ($languages as $language) {
		$translations = $GLOBALS['_ELGG']->translations[$language];

		$to_translate = array();
		if (file_exists(__DIR__ . "/languages/$language.php")) {
			$to_translate = include_once __DIR__ . "/languages/$language.php";
		}

		$original_str = $translations["groups:group"];
		$original_str_plural = $translations["groups"];

		foreach ($identifiers as $subtype => $identifier) {

			$subtype_str = $original_str;
			if (isset($translations["group:$subtype"])) {
				$subtype_str = $translations["group:$subtype"];
			}
			$to_translate["group:$subtype"] = $subtype_str;

			$subtype_str_plural = $original_str_plural;
			if (isset($translations["item:group:$subtype"])) {
				$subtype_str_plural = $translations["item:group:$subtype"];
			}
			$to_translate["item:group:$subtype"] = $subtype_str_plural;

			foreach ($translations as $key => $translation) {
				$identifier_key = preg_replace("/^(groups)/", $identifier, $key);
				if ($identifier_key == $key) {
					continue;
				}
				if (!empty($translations[$identifier_key])) {
					continue;
				}

				$translation = str_replace(strtolower($original_str), strtolower($subtype_str), $translation);
				$translation = str_replace(ucfirst($original_str), ucfirst($subtype_str), $translation);

				$translation = str_replace(strtolower($original_str_plural), strtolower($subtype_str_plural), $translation);
				$translation = str_replace(ucfirst($original_str_plural), ucfirst($subtype_str_plural), $translation);

				$to_translate[$identifier_key] = $translation;
			}
		}

		$file = fopen(__DIR__ . "/languages/$language.php", 'w');
		$contents = var_export($to_translate, true);
		fwrite($file, "<?php\r\n\r\nreturn $contents;\r\n");
		fclose($file);
	}
}
```

5. Load the website
6. You will now see new language files in `/languages/`, e.g. `/languages/en.schools.php`
7. Edit the new files to your needs. You don't need to copy anything, these files will be loaded like other translation files.
8. Remove all the code from `start.php`, as you don't want to run that code all the time
9. Flush the caches and enjoy!

## Acknowledgements

This plugin has been sponsored by [IvyTies.com](http://www.ivyties.com) - a social network platform for college admissions
