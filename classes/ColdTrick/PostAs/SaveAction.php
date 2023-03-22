<?php

namespace ColdTrick\PostAs;

/**
 * Generic action listener
 */
class SaveAction {

	protected ?\ElggUser $user;
	
	/**
	 * Prepare an action for post as
	 *
	 * @param \Elgg\Event $event 'action:validate', '<action name>'
	 *
	 * @return void
	 */
	public static function prepareAction(\Elgg\Event $event): void {
		// check for new owner_guid
		$post_as_owner_guid = get_input('post_as_owner_guid');
		if (is_array($post_as_owner_guid)) {
			// global editors get userpicker which results in an array
			$post_as_owner_guid = elgg_extract(0, $post_as_owner_guid);
		}
		
		$post_as_owner_guid = (int) $post_as_owner_guid;
		if ($post_as_owner_guid < 1) {
			// not found for create, check edit
			$guid = (int) get_input('guid');
			
			$entity = elgg_call(ELGG_IGNORE_ACCESS, function() use ($guid) {
				return get_entity($guid);
			});
			if (!$entity instanceof \ElggEntity || !$entity->canEdit()) {
				return;
			}
			
			$post_as_owner_guid = $entity->owner_guid;
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
			elgg_call(ELGG_IGNORE_ACCESS, function() use ($container_guid, $post_as_owner_guid) {
				$container = get_entity($container_guid);
				if (!$container instanceof \ElggUser) {
					return;
				}
				
				// make sure this is the new owner/container
				set_input('container_guid', $post_as_owner_guid);
			});
		}
		
		// backup session user
		$store = new static();
		$store->user = elgg_get_logged_in_user_entity();
		
		// set new session user
		$session = elgg_get_session();
		$session_manager = elgg()->session_manager;
		
		$session->set('post_as_original_user', $store->user->guid);
		$session_manager->setLoggedInUser($new_user);
		
		// register permissions
		elgg_register_event_handler('container_permissions_check', 'all', [$store, 'containerPermissions']);
		
		// register repair session function
		elgg_register_event_handler('response', "action:{$event->getType()}", [$store, 'restoreLoggedInUser'], 1);
		
		// register tracking function
		elgg_register_event_handler('create', 'all', [$store, 'trackPostAs']);
		elgg_register_event_handler('update', 'all', [$store, 'trackPostAs']);
		elgg_register_event_handler('create', 'all', [$store, 'addPostAsUserToSubscribers']);
		elgg_register_event_handler('update', 'all', [$store, 'addPostAsUserToSubscribers']);
	}
	
	/**
	 * Restore the logged-in user to the user before the action
	 *
	 * @return void
	 */
	public function restoreLoggedInUser(): void {
		if (!$this->user instanceof \ElggUser) {
			return;
		}
		
		$session = elgg_get_session();
		$session_manager = elgg()->session_manager;
		
		$session->remove('post_as_original_user');
		$session_manager->setLoggedInUser($this->user);
	}
	
	/**
	 * Track that this entity was created on behalf of somebody else
	 *
	 * @param \Elgg\Event $event 'create|update', 'all'
	 *
	 * @return void
	 */
	public function trackPostAs(\Elgg\Event $event): void {
		if (!$this->user instanceof \ElggUser) {
			return;
		}
		
		$entity = $event->getObject();
		if (!$entity instanceof \ElggEntity) {
			return;
		}
		
		// clear sticky form
		elgg_clear_sticky_form('post_as');
		
		if (!post_as_is_supported($entity->getType(), $entity->getSubtype())) {
			return;
		}
		
		$entity->post_as_actor = $this->user->guid;
	}
	
	/**
	 * Add post as user to content subscribers
	 *
	 * @param \Elgg\Event $event 'create|update', 'all'
	 *
	 * @return void
	 */
	public function addPostAsUserToSubscribers(\Elgg\Event $event): void {
		$entity = $event->getObject();
		if (!$entity instanceof \ElggEntity) {
			return;
		}
		
		$post_as_guid = $entity->post_as_actor;
		if (empty($post_as_guid)) {
			return;
		}
		
		$content_preferences = $this->user->getNotificationSettings('content_create');
		$enabled_methods = array_keys(array_filter($content_preferences));
		if (empty($enabled_methods)) {
			return;
		}
		
		$entity->addSubscription($post_as_guid, $enabled_methods);
	}
	
	/**
	 * Check permissions for original user when needed
	 *
	 * @param \Elgg\Event $event 'container_permissions_check', 'all'
	 *
	 * @return null|bool
	 */
	public function containerPermissions(\Elgg\Event $event): ?bool {
		if ($event->getValue()) {
			// already allowed
			return null;
		}
		
		if (!$this->user instanceof \ElggUser) {
			// no idea how we got here
			return null;
		}
		
		$container = $event->getParam('container');
		$user = $event->getParam('user');
		$subtype = $event->getParam('subtype');
		if (!$container instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return null;
		}
		
		if ($user->guid === $this->user->guid) {
			// prevent recursion
			return null;
		}
		
		if (!post_as_is_supported($event->getType(), $subtype)) {
			// not allowed for this type/subtype
			return null;
		}
		
		if (!post_as_is_authorized($user->guid, $this->user->guid)) {
			// user is not authorized, how did we get here
			return null;
		}
		
		// check permissions of original user
		if (!$container->canWriteToContainer($this->user->guid, $event->getType(), $subtype)) {
			return null;
		}
		
		return true;
	}
}
