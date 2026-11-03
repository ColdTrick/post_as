<?php

namespace ColdTrick\PostAs;

/**
 * Listen to user events
 */
class User {
	
	/**
	 * Cache the last x number of entities the user posted as somebody else so we can show those in listings
	 *
	 * @param \Elgg\Event $event 'login:after', 'user'
	 *
	 * @return void
	 */
	public static function loginAfter(\Elgg\Event $event): void {
		$user = $event->getObject();
		if (!$user instanceof \ElggUser) {
			return;
		}
		
		if (!post_as_get_posters($user->guid, true) && !post_as_is_global_editor($user->guid)) {
			return;
		}
		
		$entities = elgg_call(ELGG_IGNORE_ACCESS, function() use ($user) {
			return elgg_get_entities([
				'access_id' => ACCESS_PRIVATE,
				'metadata_name_value_pairs' => [
					'name' => 'post_as_actor',
					'value' => $user->guid,
				],
				'limit' => 10,
				'callback' => function ($row) {
					return (int) $row->guid;
				},
			]);
		});
		if (empty($entities)) {
			return;
		}
		
		elgg()->session->set('post_as_guids', $entities);
	}
}
