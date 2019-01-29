<?php

namespace ColdTrick\PostAs;

class Permissions {
	
	/**
	 * Give users edit rights on for granted users content
	 *
	 * @param \Elgg\Hook $hook 'permissions_check', 'all'
	 *
	 * @return void|bool
	 */
	public static function canEdit(\Elgg\Hook $hook) {
		
		if ($hook->getValue()) {
			// already has access
			return;
		}
		
		if (elgg_get_plugin_setting('allow_edit', 'post_as') !== 'yes') {
			return;
		}
		
		$entity = $hook->getEntityParam();
		$user = $hook->getParam('user');
		if (!$entity instanceof \ElggEntity || !$user instanceof \ElggUser) {
			return;
		}
		
		if ($entity->owner_guid === $user->guid) {
			// how did we get here?
			return;
		}
		
		if (!post_as_is_supported($entity->getType(), $entity->getSubtype())) {
			// type/subtype is not supported
			return;
		}
		
		if (!post_as_is_authorized($entity->owner_guid, $user->guid)) {
			// not authorized for this user
			return;
		}
		
		return true;
	}
	
	/**
	 * Allow editors to write to containers
	 *
	 * @param \Elgg\Hook $hook 'container_permissions_check', 'all'
	 *
	 * @return void|true
	 */
	public static function canWriteToContainer(\Elgg\Hook $hook) {
		
		if ($hook->getValue()) {
			// already allowed
			return;
		}
		
		$container = $hook->getParam('container');
		$user = $hook->getParam('user');
		$subtype = $hook->getParam('subtype');
		if (!$container instanceof \ElggUser || !$user instanceof \ElggUser) {
			// container needs to be a user
			return;
		}
		
		if (!post_as_is_supported($hook->getType(), $subtype)) {
			// not supported for type/subtype
			return;
		}
		
		if (!post_as_is_authorized($container->guid, $user->guid)) {
			// not autorized for this user
			return;
		}
		
		return true;
	}
}
