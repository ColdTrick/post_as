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

/**
 * Get all the users the given user_guid is authorized to post on behalf of
 *
 * @param int $user_guid the user to fetch for (default: current user)
 *
 * @return ElggUser[]|int[]
 */
function post_as_get_posters($user_guid = 0, $guid_only = false) {
	
	$guid_only = (bool) $guid_only;
	$user_guid = (int) $user_guid;
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($user_guid < 1) {
		return [];
	}
	
	$options = [
		'type' => 'user',
		'limit' => false,
		'relationship' => POST_AS_RELATIONSHIP,
		'relationship_guid' => $user_guid,
	];
	if ($guid_only) {
		$options['callback'] = function($row) {
			return (int) $row->guid;
		};
	} else {
		$dbprefix = elgg_get_config('dbprefix');
		
		$options['joins'] = [
			"JOIN {$dbprefix}users_entity ue ON e.guid = ue.guid",
		];
		$options['order_by'] = 'ue.name ASC';
	}
	
	return elgg_get_entities_from_relationship($options);
}

/**
 * Can a user post as another user
 *
 * @param int $post_as_guid post on behalf of
 * @param int $user_guid    the user who wishes to post
 *
 * @return bool
 */
function post_as_is_authorized($post_as_guid, $user_guid = 0) {
	
	$post_as_guid = (int) $post_as_guid;
	$user_guid = (int) $user_guid;
	
	if ($post_as_guid < 1) {
		return false;
	}
	
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($user_guid < 1) {
		return false;
	}
	
	return (bool) check_entity_relationship($user_guid, POST_AS_RELATIONSHIP, $post_as_guid);
}
