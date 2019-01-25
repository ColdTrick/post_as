<?php

namespace ColdTrick\PostAs;

use Elgg\DefaultPluginBootstrap;

class Bootstrap extends DefaultPluginBootstrap {
	
	/**
	 * {@inheritDoc}
	 * @see \Elgg\DefaultPluginBootstrap::init()
	 */
	public function init() {
		
		// extend views
		elgg_extend_view('forms/blog/save', 'post_as/input');
		elgg_extend_view('forms/static/edit', 'post_as/input');
	}
	
	/**
	 * Register plugin hooks
	 */
	protected function registerHooks() {
		$hooks = $this->elgg()->hooks;
		
		$hooks->registerHandler('action:validate', 'blog/save', __NAMESPACE__ . '\SaveAction::prepareAction');
		$hooks->registerHandler('action:validate', 'static/edit', __NAMESPACE__ . '\SaveAction::prepareAction');
		$hooks->registerHandler('permissions_check', 'object', __NAMESPACE__ . '\Permissions::canEdit');
	}
}
