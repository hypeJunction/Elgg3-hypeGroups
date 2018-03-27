<?php

namespace hypeJunction\Groups;

use hypeJunction\Lists\Collection;
use hypeJunction\Lists\Filters\All;
use hypeJunction\Lists\Filters\IsAdministeredBy;
use hypeJunction\Lists\Filters\IsMember;
use hypeJunction\Lists\Sorters\Alpha;
use hypeJunction\Lists\Sorters\MemberCount;
use hypeJunction\Lists\Sorters\TimeCreated;

class DefaultGroupCollection extends Collection {

	/**
	 * Get ID of the collection
	 * @return string
	 */
	public function getId() {
		$subtypes = (array) $this->getSubtypes();
		$subtype = array_shift($subtypes);

		return "collection:group:$subtype:all";
	}

	/**
	 * Get title of the collection
	 * @return string
	 */
	public function getDisplayName() {
		$identifier = $this->getPageIdentifier();

		return elgg_echo("$identifier:all");
	}

	/**
	 * Get the type of collection, e.g. owner, friends, group all
	 * @return string
	 */
	public function getCollectionType() {
		return 'all';
	}

	/**
	 * Get type of entities in the collection
	 * @return mixed
	 */
	public function getType() {
		return 'group';
	}

	/**
	 * Get subtypes of entities in the collection
	 * @return string|string[]
	 */
	public function getSubtypes() {
		$svc = elgg()->groups;

		/* @var $svc GroupsService */

		return $svc->getSubtypes($this->getPageIdentifier());
	}

	/**
	 * Sniff page identifier from page URL
	 *
	 * @return string
	 */
	protected function getPageIdentifier() {
		$svc = elgg()->groups;

		/* @var $svc GroupsService */

		return $svc->getPageIdentifier();
	}

	/**
	 * Get default query options
	 *
	 * @param array $options Query options
	 *
	 * @return array
	 */
	public function getQueryOptions(array $options = []) {
		return $options;
	}

	/**
	 * Get default list view options
	 *
	 * @param array $options List view options
	 *
	 * @return mixed
	 */
	public function getListOptions(array $options = []) {
		$identifier = $this->getPageIdentifier();

		return array_merge([
			'no_results' => elgg_echo("$identifier:none"),
			'full_view' => false,
			'list_type' => 'list',
			'list_class' => 'elgg-groups',
		], $options);
	}

	/**
	 * Returns base URL of the collection
	 *
	 * @return string
	 */
	public function getURL() {
		return elgg_generate_url($this->getId());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSearchOptions() {
		return parent::getSearchOptions();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSortOptions() {
		return [
			Alpha::id() => Alpha::class,
			TimeCreated::id() => TimeCreated::class,
			MemberCount::id() => MemberCount::class,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilterOptions() {
		if (!elgg_is_logged_in() || $this->getCollectionType() != 'all') {
			return [];
		}

		return [
			All::id() => All::class,
			IsAdministeredBy::id() => IsAdministeredBy::class,
			IsMember::id() => IsMember::class,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getMenu() {

		$type = $this->getType();
		$subtypes = (array) $this->getSubtypes();

		$target = $this->getTarget();

		$menu = [];

		foreach ($subtypes as $subtype) {

			$owner = $target;

			if (!$owner || ($owner instanceof \ElggUser && $owner->guid != $target->guid)) {
				$owner = elgg_get_logged_in_user_entity();
			}

			if (!$owner) {
				return [];
			}

			// do we have an owner and is the current user allowed to create content here
			if (!$owner->canWriteToContainer(0, $type, $subtype)) {
				continue;
			}

			$href = elgg_generate_url("add:$type:$subtype", [
				'container_guid' => $owner->guid,
			]);

			if (!$href) {
				continue;
			}

			$text = elgg_echo("add:$type:$subtype");

			// register the title menu item
			$menu[] = \ElggMenuItem::factory([
				'name' => 'add',
				'icon' => 'plus',
				'href' => $href,
				'text' => $text,
			]);
		}

		return $menu;
	}
}