<?php

namespace ColdTrick\PostAs;

use ColdTrick\PostAs\Middleware\PostAs;

/**
 * Route configuration event listener
 */
class RouteConfig {

	/**
	 * Add middleware to all routes
	 *
	 * @param \Elgg\Event $event 'route:config', 'all'
	 *
	 * @return array
	 */
	public static function addPostAsMiddleware(\Elgg\Event $event): array {
		$config = $event->getValue();
		
		$middleware = (array) elgg_extract('middleware', $config, []);
		
		$middleware[] = PostAs::class;
		
		$config['middleware'] = $middleware;
		
		return $config;
	}
}
