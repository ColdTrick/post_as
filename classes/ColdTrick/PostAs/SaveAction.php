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
	 * @param \Elgg\Hook $hook 'action:validate', '<action name>'
	 *
	 * @return void
	 */
	public static function prepareAction(\Elgg\Hook $hook) {
		
		// check for new owner_guid
		$post_as_owner_guid = get_input('post_as_owner_guid');
		if (is_array($post_as_owner_guid)) {
			// global editors get userpicker which results in an array
			$post_as_owner_guid = elgg_extract(0, $post_as_owner_guid);
		}
		$post_as_owner_guid = (int) $post_as_owner_guid;
		if ($post_as_owner_guid < 1) {
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
			elgg_call(ELGG_IGNORE_ACCESS, function() use ($container_guid, $post_as_owner_guid) {
				$container = get_entity($container_guid);
				if (!$container instanceof \ElggUser) {
					return;
				}
				
				// make sure this is the new owner/container
				$request = _elgg_services()->request;
				$request->query->set('container_guid', $post_as_owner_guid);
			});
		}
		
		// backup session user
		$store = new static();
		$store->user = elgg_get_logged_in_user_entity();
		
		// set new session user
		$session = elgg_get_session();
		
		$session->setLoggedInUser($new_user);
		
		// register permissions
		elgg_register_plugin_hook_handler('container_permissions_check', 'all', [$store, 'containerPermissions']);
		
		// register repair session function
		elgg_register_event_handler('shutdown', 'system', [$store, 'restoreLoggedInUser'], 1);
		
		// register tracking function
		elgg_register_event_handler('create', 'all', [$store, 'trackPostAs']);
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
	 * Track that this entity was create on behalf of somebody else
	 *
	 * @param \Elgg\Event $event 'create', 'all'
	 *
	 * @return void
	 */
	public function trackPostAs(\Elgg\Event $event) {
		
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
	 * Check permissions for original user when needed
	 *
	 * @return void|true
	 */
	public function containerPermissions(\Elgg\Hook $hook) {
		
		if ($hook->getValue()) {
			// already allowed
			return;
		}
		
		if (!$this->user instanceof \ElggUser) {
			// no idea how we got here
			return;
		}
		
		$container = $hook->getParam('container');
		$user = $hook->getParam('user');
		$subtype = $hook->getParam('subtype');
		if (!$container instanceof \ElggGroup || !$user instanceof \ElggUser) {
			return;
		}
		
		if ($user->guid === $this->user->guid) {
			// prevent recursion
			return;
		}
		
		if (!post_as_is_supported($hook->getType(), $subtype)) {
			// not allowed for this type/subtype
			return;
		}
		
		if (!post_as_is_authorized($user->guid, $this->user->guid)) {
			// user is not authorized, how did we get here
			return;
		}
		
		// check permissions of original user
		if (!$container->canWriteToContainer($this->user->guid, $hook->getType(), $subtype)) {
			return;
		}
		
		return true;
	}
}
