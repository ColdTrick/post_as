<?php

namespace ColdTrick\PostAs\Menus;

use Elgg\Menu\MenuItems;

class OwnerBlock {
	
	/**
	 * Add a menu item to the owner lock of a user
	 *
	 * @param \Elgg\Hook $hook 'register', 'menu:owner_block'
	 *
	 * @return void|MenuItems
	 */
	public static function addPostedAs(\Elgg\Hook $hook) {
		
		$entity = $hook->getEntityParam();
		if (!$entity instanceof \ElggUser || !$entity->canEdit()) {
			return;
		}
		
		if (elgg_get_plugin_setting('allow_edit', 'post_as') !== 'yes') {
			return;
		}

		/* @var $result MenuItems */
		$result = $hook->getValue();
		
		$result[] = \ElggMenuItem::factory([
			'name' => 'posted_as',
			'text' => elgg_echo('post_as:menu:posted_as'),
			'href' => elgg_generate_url('collection:post_as:owner', [
				'username' => $entity->username,
			]),
			'priority' => 1000,
		]);
		
		return $result;
	}
}
