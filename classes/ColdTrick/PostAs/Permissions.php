<?php

namespace ColdTrick\PostAs;

/**
 * Permission event listener
 */
class Permissions {
	
	/**
	 * Give users edit rights on for granted users content
	 *
	 * @param \Elgg\Event $event 'permissions_check', 'all'
	 *
	 * @return null|bool
	 */
	public static function canEdit(\Elgg\Event $event): ?bool {
		if ($event->getValue()) {
			// already has access
			return null;
		}
		
		if (elgg_get_plugin_setting('allow_edit', 'post_as') !== 'yes') {
			return null;
		}
		
		$entity = $event->getEntityParam();
		$user = $event->getParam('user');
		if (!$entity instanceof \ElggEntity || !$user instanceof \ElggUser) {
			return null;
		}
		
		if ($entity->owner_guid === $user->guid) {
			// how did we get here?
			return null;
		}
		
		if (!post_as_is_supported($entity->getType(), $entity->getSubtype())) {
			// type/subtype is not supported
			return null;
		}
		
		if (!post_as_is_authorized($entity->owner_guid, $user->guid)) {
			// not authorized for this user
			return null;
		}
		
		return true;
	}
	
	/**
	 * Allow editors to write to containers
	 *
	 * @param \Elgg\Event $event 'container_permissions_check', 'all'
	 *
	 * @return null|bool
	 */
	public static function canWriteToContainer(\Elgg\Event $event): ?bool {
		if ($event->getValue()) {
			// already allowed
			return null;
		}
		
		$container = $event->getParam('container');
		$user = $event->getParam('user');
		$subtype = $event->getParam('subtype');
		if (!$container instanceof \ElggUser || !$user instanceof \ElggUser) {
			// container needs to be a user
			return null;
		}
		
		if (!post_as_is_supported($event->getType(), $subtype)) {
			// not supported for type/subtype
			return null;
		}
		
		if (!post_as_is_authorized($container->guid, $user->guid)) {
			// not authorized for this user
			return null;
		}
		
		return true;
	}
}
