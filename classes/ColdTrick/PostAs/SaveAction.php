<?php

namespace ColdTrick\PostAs;

class SaveAction {

	/**
	 * @var \ElggUser
	 */
	protected $user;
	
	/**
	 * Prepare an action for post as
	 *
	 * @param string $hook   the name of the hook
	 * @param string $type   the type of the hook
	 * @param mixed  $return current return value
	 * @param mixed  $params supplied params
	 *
	 * @return void
	 */
	public static function prepareAction($hook, $type, $return, $params) {
		
		// check for new owner_guid
		$post_as_owner_guid = (int) get_input('post_as_owner_guid');
		if (empty($post_as_owner_guid)) {
			return;
		}
		
		// not current user
		if ($post_as_owner_guid === elgg_get_logged_in_user_guid()) {
			return;
		}
		
		// is authorized
		if (!post_as_is_authorized($post_as_owner_guid)) {
			// not authorized
			return;
		}
		
		// check for user
		$new_user = get_user($post_as_owner_guid);
		if (!$new_user instanceof \ElggUser) {
			return;
		}
		
		// sticky form
		elgg_make_sticky_form('post_as');
				
		// check container_guid === user
		$container_guid = (int) get_input('container_guid');
		if (!empty($container_guid)) {
			$ia = elgg_set_ignore_access(true);
			
			$container = get_entity($container_guid);
			if ($container instanceof \ElggUser) {
				// make sure this is the new owner/container
				set_input('container_guid', $post_as_owner_guid);
			}
			
			elgg_set_ignore_access($ia);
		}
		
		// backup session user
		$store = new static();
		$store->user = elgg_get_logged_in_user_entity();
		
		// set new session user
		$session = elgg_get_session();
		
		$session->setLoggedInUser($new_user);
		
		// register repair session function
		elgg_register_event_handler('shutdown', 'system', [$store, 'restoreLoggedInUser'], 1);
		
		// register tracking function
		// @todo support groups?
		elgg_register_event_handler('create', 'object', [$store, 'trackPostAs']);
	}
	
	/**
	 * Restore the logged in user to the user before the action
	 *
	 * @return void
	 */
	public function restoreLoggedInUser() {
		
		if (!$this->user instanceof \ElggUser) {
			return;
		}
		
		$session = elgg_get_session();
		$session->setLoggedInUser($this->user);
	}
	
	/**
	 * Track that this entity was create on behald of somebody else
	 *
	 * @param string      $event  the name of the event
	 * @param string      $type   the type of the event
	 * @param \ElggEntity $object supplied entity
	 *
	 * @return void
	 */
	public function trackPostAs($event, $type, $object) {
		
		if (!$this->user instanceof \ElggUser) {
			return;
		}
		
		// clear sticky form
		elgg_clear_sticky_form('post_as');
		
		// @todo make this configurable
		if (!in_array($object->getSubtype(), ['blog'])) {
			return;
		}
		
		$object->post_as_actor = $this->user->guid;
	}
}
