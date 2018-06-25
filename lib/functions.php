<?php
/**
 * All helper functions are bundled here
 */

/**
 * Get all the users which have been autorized by the given user_guid
 *
 * @param int  $user_guid the user_guid to get users for
 * @param bool $guid_only return only guids
 *
 * @return ElggUser[]|int[]
 */
function post_as_get_authorized_users($user_guid, $guid_only = false) {
	
	$user_guid = (int) $user_guid;
	if ($user_guid < 1) {
		return [];
	}
	
	$guid_only = (bool) $guid_only;
	
	$options = [
		'type' => 'user',
		'limit' => false,
		'relationship' => POST_AS_RELATIONSHIP,
		'relationship_guid' => $user_guid,
		'inverse_relationship' => true,
	];
	
	if ($guid_only) {
		$options['callback'] = function($row) {
			return (int) $row->guid;
		};
	}
	
	return elgg_get_entities_from_relationship($options);
}
