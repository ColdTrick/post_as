<?php

namespace ColdTrick\PostAs;

class Permissions {
	
	/**
	 * Give users edit rights on for granted users content
	 *
	 * @param \Elgg\Hook $hook 'permissions_check', 'object'
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
		
		$posters = post_as_get_posters($user->guid, true);
		if (empty($posters)) {
			return;
		}
		
		return in_array($entity->owner_guid, $posters);
	}
}
