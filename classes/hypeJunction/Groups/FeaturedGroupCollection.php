<?php

namespace hypeJunction\Groups;

use hypeJunction\Lists\Filters\IsFeatured;

class FeaturedGroupCollection extends DefaultGroupCollection {

	/**
	 * {@inheritdoc}
	 */
	public function getId() {
		return 'collection:group:featured';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCollectionType() {
		return 'featured';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getURL() {
		return elgg_generate_url($this->getId());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName() {
		return elgg_echo('groups:featured');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQueryOptions(array $options = []) {
		$this->addFilter(IsFeatured::class);

		return parent::getQueryOptions($options);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getListOptions(array $options = []) {
		return parent::getListOptions($options);
	}

}