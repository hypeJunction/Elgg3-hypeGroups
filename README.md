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

## Acknowledgements

This plugin has been sponsored by [IvyTies.com](http://www.ivyties.com) - a social network platform for college admissions