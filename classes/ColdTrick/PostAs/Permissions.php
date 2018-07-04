<?php

namespace ColdTrick\PostAs;

class Permissions {
	
	/**
	 * Give users edit rights on for granted users content
	 *
	 * @param string $hook   the name of the hook
	 * @param string $type   the type of the hook
	 * @param bool   $return current return value
	 * @param array  $params supplied params
	 *
	 * @return void|bool
	 */
	public static function canEdit($hook, $type, $return, $params) {
		
		if (elgg_get_plugin_setting('allow_edit', 'post_as') !== 'yes') {
			return;
		}
		
		if ($return) {
			// already has access
			return;
		}
		
		$entity = elgg_extract('entity', $params);
		$user = elgg_extract('user', $params);
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
