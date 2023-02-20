<?php

namespace ColdTrick\PostAs;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 */
	public function boot() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('route:config', 'all', __NAMESPACE__ . '\RouteConfig::addPostAsMiddleware');
		
		$this->validateSession();
	}
	
	/**
	 * {@inheritDoc}
	 */
	public function ready() {
		$this->proccessConfig();
	}
	
	/**
	 * Get the post as configuration and process the configuration
	 *
	 * @return void
	 */
	protected function proccessConfig(): void {
		$hooks = $this->elgg()->hooks;
		
		$config = post_as_get_config();
		foreach ($config as $form_name => $settings) {
			// extend form (if needed)
			if ((bool) elgg_extract('extend_form', $settings, true)) {
				elgg_extend_view("forms/{$form_name}", 'post_as/input');
			}
			
			// register action listener for correct handling
			$action = elgg_extract('action', $settings, $form_name);
			$hooks->registerHandler('action:validate', $action, __NAMESPACE__ . '\SaveAction::prepareAction');
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
		if (!$session->has('post_as_original_user')) {
			return;
		}
		
		$original_user_guid = (int) $session->get('post_as_original_user');
		$original_user = get_user($original_user_guid);
		if (!$original_user instanceof \ElggUser) {
			$session->remove('post_as_original_user');
			return;
		}
		
		if ($session->getLoggedInUserGuid() === $original_user->guid) {
			// how did we get here?
			$session->remove('post_as_original_user');
			return;
		}
		
		// restore correct user
		$session->setLoggedInUser($original_user);
		$session->remove('post_as_original_user');
	}
}
