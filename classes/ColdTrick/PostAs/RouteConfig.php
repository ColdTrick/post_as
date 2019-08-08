<?php

namespace ColdTrick\PostAs;

use ColdTrick\PostAs\Middleware\PostAs;

class RouteConfig {

	/**
	 * Add middleware to all routes
	 *
	 * @param \Elgg\Hook $hook 'route:config', 'all'
	 *
	 * @return array
	 */
	public static function addPostAsMiddleware(\Elgg\Hook $hook) {
		
		$config = $hook->getValue();
		
		$middleware = elgg_extract('middleware', $config, []);
		
		$middleware[] = PostAs::class;
		
		$config['middleware'] = $middleware;
		
		return $config;
	}
}
