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
function post_as_get_authorized_users(int $user_guid, bool $guid_only = false): array {
	
	if ($user_guid < 1) {
		return [];
	}
	
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
	
	return elgg_get_entities($options);
}

/**
 * Get all the users the given user_guid is authorized to post on behalf of
 *
 * @param int  $user_guid the user to fetch for (default: current user)
 * @param bool $guid_only return only guids
 *
 * @return ElggUser[]|int[]
 */
function post_as_get_posters(int $user_guid = 0, bool $guid_only = false): array {
	
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
		'metadata_name_value_pairs' => [
			[
				'name' => 'banned',
				'value' => 'no',
			],
		],
	];
	if ($guid_only) {
		$options['callback'] = function($row) {
			return (int) $row->guid;
		};
	} else {
		$options['order_by_metadata'] = [
			'name' => 'name',
			'direction' => 'ASC',
		];
	}
	
	return elgg_get_entities($options);
}

/**
 * Can a user post as another user
 *
 * @param int $post_as_guid post on behalf of
 * @param int $user_guid    the user who wishes to post
 *
 * @return bool
 */
function post_as_is_authorized(int $post_as_guid, int $user_guid = 0): bool {
	
	if ($post_as_guid < 1) {
		return false;
	}
	
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($user_guid < 1) {
		return false;
	}
	
	if (post_as_is_global_editor($user_guid)) {
		return true;
	}
	
	return (bool) check_entity_relationship($user_guid, POST_AS_RELATIONSHIP, $post_as_guid);
}

/**
 * Check if the given user is a global editor
 *
 * @param int $user_guid the user_guid to check (default: current user)
 *
 * @return bool
 */
function post_as_is_global_editor(int $user_guid = 0): bool {
	static $editors;
	
	if ($user_guid < 1) {
		$user_guid = elgg_get_logged_in_user_guid();
	}
	
	if ($user_guid < 1) {
		return false;
	}
	
	if (!isset($editors)) {
		$editors = [];
		
		$setting = elgg_get_plugin_setting('editor_guids', 'post_as');
		if (!empty($setting)) {
			$editors = string_to_tag_array($setting);
			
			array_walk($editors, function(&$guid) {
				$guid = (int) $guid;
			});
		}
	}
	
	return in_array($user_guid, $editors);
}

/**
 * Get the configuration for post as support
 *
 * @return array
 */
function post_as_get_config(): array {
	$defaults = [
		'blog/save' => [
			'type' => 'object',
			'subtype' => 'blog',
		],
		'static/edit' => [
			'type' => 'object',
			'subtype' => 'static',
		],
	];
	
	return elgg_trigger_plugin_hook('config', 'post_as', null, $defaults);
}

/**
 * Check if post as is supported for the given type/subtype
 *
 * @param string $entity_type    the entity type to check
 * @param string $entity_subtype the entity subtype to check
 *
 * @return bool
 */
function post_as_is_supported(string $entity_type, string $entity_subtype): bool {
	
	if (empty($entity_type) || empty($entity_subtype)) {
		return false;
	}
	
	$config = post_as_get_config();
	if (empty($config) || !is_array($config)) {
		return false;
	}
	
	foreach ($config as $form => $settings) {
		
		if (elgg_extract('type', $settings) !== $entity_type) {
			continue;
		}
		
		if (elgg_extract('subtype', $settings) !== $entity_subtype) {
			continue;
		}
		
		return true;
	}
	
	return false;
}
