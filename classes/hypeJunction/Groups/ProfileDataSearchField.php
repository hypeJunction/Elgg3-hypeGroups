<?php

namespace hypeJunction\Groups;

use hypeJunction\Lists\SearchFields\SearchField;

class ProfileDataSearchField extends SearchField {

	/**
	 * Returns field name
	 * @return string
	 */
	public function getName() {
		return 'profile';
	}

	/**
	 * Returns field parameters
	 * @return array|null
	 */
	public function getField() {
		$name = $this->getName();
		$value = $this->getValue() ? : [];

		$view = elgg_view('input/search/group_profile_data', [
			'field' => $this,
			'name' => $name,
			'value' => $value,
		]);

		return [
			'#html' => $view,
		];
	}

	/**
	 * Set constraints on the collection based on field value
	 * @return void
	 */
	public function setConstraints() {
		$value = $this->getValue();
		if (!$value) {
			return;
		}

		$this->collection->addFilter(ProfileDataSearchFilter::class, null, [
			'profile' => $value,
		]);
	}
}