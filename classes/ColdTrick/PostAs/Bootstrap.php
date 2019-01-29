<?php

namespace ColdTrick\PostAs;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\DefaultPluginBootstrap::init()
	 */
	public function init() {
		$this->registerHooks();
	}
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\DefaultPluginBootstrap::ready()
	 */
	public function ready() {
		$this->proccessConfig();
	}
	
	/**
	 * Register plugin hooks
	 *
	 * @return void
	 */
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('container_permissions_check', 'all', __NAMESPACE__ . '\Permissions::canWriteToContainer');
		$hooks->registerHandler('permissions_check', 'all', __NAMESPACE__ . '\Permissions::canEdit');
		$hooks->registerHandler('setting', 'plugin', __NAMESPACE__ . '\PluginSettings::convertArrayToString');
	}
	
	protected function proccessConfig() {
		$config = post_as_get_config();
		if (empty($config) || empty($config)) {
			return;
		}
		
		$hooks = $this->elgg()->hooks;
		
		foreach ($config as $form_name => $settings) {
			
			// extend form (if needed)
			if (elgg_extract('extend_form', $settings, true)) {
				elgg_extend_view("forms/{$form_name}", 'post_as/input');
			}
			
			// register action listener for correct handling
			$action = elgg_extract('action', $settings, $form_name);
			$hooks->registerHandler('action:validate', $action, __NAMESPACE__ . '\SaveAction::prepareAction');
		}
	}
}
