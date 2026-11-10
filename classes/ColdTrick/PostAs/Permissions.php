<?php

namespace ColdTrick\PostAs;

use Elgg\Database\EntityTable;
use Elgg\Database\QueryBuilder;

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
	
	/**
	 * Add post as GUIDs to access SQL
	 *
	 * @param \Elgg\Event $event 'get_sql', 'access'
	 *
	 * @return array|null
	 */
	public static function accessSQL(\Elgg\Event $event): ?array {
		if (!elgg_is_logged_in()) {
			return null;
		}
		
		$ignore_access = $event->getParam('ignore_access');
		$user_guid = $event->getParam('user_guid');
		if ($ignore_access || $user_guid !== elgg_get_logged_in_user_guid()) {
			return null;
		}
		
		$qb = $event->getParam('query_builder');
		$guid_column = $event->getParam('guid_column');
		if (!$qb instanceof QueryBuilder || empty($guid_column)) {
			return null;
		}
		
		$session = elgg()->session;
		if (!$session->isStarted()) {
			return null;
		}
		
		if (!$session->has('post_as_guids')) {
			$session->set('post_as_guids', []);
			if (post_as_get_posters($user_guid, true) || post_as_is_global_editor($user_guid)) {
				$entities = elgg_call(ELGG_IGNORE_ACCESS, function() use ($user_guid) {
					return elgg_get_entities([
						'access_id' => ACCESS_PRIVATE,
						'metadata_name_value_pairs' => [
							'name' => 'post_as_actor',
							'value' => $user_guid,
						],
						'limit' => 10,
						'callback' => function ($row) {
							return (int) $row->guid;
						},
					]);
				});
				if (!empty($entities)) {
					$session->set('post_as_guids', $entities);
				}
			}
		}
		
		$post_as_guids = $session->get('post_as_guids');
		if (empty($post_as_guids)) {
			return null;
		}
		
		$table_alias = $event->getParam('table_alias');
		$table_alias = $table_alias ? "{$table_alias}." : '';
		
		$parts = $event->getValue();
		
		$parts['ors'] = elgg_extract('ors', $parts, []);
		$parts['ors']['post_as_guids'] = $qb->compare("{$table_alias}{$guid_column}", 'in', $post_as_guids, ELGG_VALUE_GUID);
		
		return $parts;
	}
}
