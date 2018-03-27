<?php

namespace hypeJunction\Groups;

use hypeJunction\Lists\Filters\IsMember;

class JoinedGroupCollection extends DefaultGroupCollection {

	/**
	 * {@inheritdoc}
	 */
	public function getId() {
		$subtypes = (array) $this->getSubtypes();
		$subtype = array_shift($subtypes);

		return "collection:group:$subtype:member";
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCollectionType() {
		return 'member';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getURL() {
		return elgg_generate_url($this->getId(), [
			'username' => $this->getTarget()->username,
		]);
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName() {
		$identifier = $this->getPageIdentifier();

		if ($this->getTarget()->guid == elgg_get_logged_in_user_guid()) {
			return elgg_echo("$identifier:yours");
		} else if ($this->getTarget()) {
			return elgg_echo("$identifier:user", [$this->getTarget()->getDisplayName()]);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getQueryOptions(array $options = []) {
		$this->addFilter(IsMember::class, $this->getTarget());

		return parent::getQueryOptions($options);
	}

}