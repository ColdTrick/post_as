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
}
