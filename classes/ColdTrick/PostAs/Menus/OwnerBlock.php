<?php

namespace ColdTrick\PostAs\Menus;

use Elgg\Menu\MenuItems;

/**
 * Add menu items to the owner_block menu
 */
class OwnerBlock {
	
	/**
	 * Add a menu item to the owner block of a user
	 *
	 * @param \Elgg\Event $event 'register', 'menu:owner_block'
	 *
	 * @return null|MenuItems
	 */
	public static function addPostedAs(\Elgg\Event $event): ?MenuItems {
		$entity = $event->getEntityParam();
		if (!$entity instanceof \ElggUser || !$entity->canEdit()) {
			return null;
		}
		
		if (elgg_get_plugin_setting('allow_edit', 'post_as') !== 'yes') {
			return null;
		}

		/* @var $result MenuItems */
		$result = $event->getValue();
		
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
