<?php

namespace ColdTrick\PostAs;

use Elgg\DefaultPluginBootstrap;

/**
 * Plugin bootstrap
 */
class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		$events = $this->elgg()->events;
		
		$events->registerHandler('route:config', 'all', __NAMESPACE__ . '\RouteConfig::addPostAsMiddleware');
		
		$this->validateSession();
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function ready() {
		$this->processConfig();
	}
	
	/**
	 * Get the post as configuration and process the configuration
	 *
	 * @return void
	 */
	protected function processConfig(): void {
		$events = $this->elgg()->events;
		
		$config = post_as_get_config();
		foreach ($config as $form_name => $settings) {
			// extend form (if needed)
			if ((bool) elgg_extract('extend_form', $settings, true)) {
				elgg_extend_view("forms/{$form_name}", 'post_as/input');
			}
			
			// register action listener for correct handling
			$action = elgg_extract('action', $settings, $form_name);
			$events->registerHandler('action:validate', $action, __NAMESPACE__ . '\SaveAction::prepareAction');
		}
	}
	
	/**
	 * Make sure the correct user is logged in
	 *
	 * This could happen if during the action a fatal error occurred which prevented the recovery of the original user
	 *
	 * @return void
	 */
	protected function validateSession(): void {
		$session = $this->elgg()->session;
		$session_manager = $this->elgg()->session_manager;
		if (!$session->has('post_as_original_user')) {
			return;
		}
		
		$original_user_guid = (int) $session->get('post_as_original_user');
		$original_user = get_user($original_user_guid);
		if (!$original_user instanceof \ElggUser) {
			$session->remove('post_as_original_user');
			return;
		}
		
		if ($session_manager->getLoggedInUserGuid() === $original_user->guid) {
			// how did we get here?
			$session->remove('post_as_original_user');
			return;
		}
		
		// restore correct user
		$session_manager->setLoggedInUser($original_user);
		$session->remove('post_as_original_user');
	}
}
