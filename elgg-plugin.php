<?php

use ColdTrick\PostAs\Bootstrap;
use Elgg\Router\Middleware\Gatekeeper;

define('POST_AS_RELATIONSHIP', 'can_post_as');

require_once(__DIR__ . '/lib/functions.php');

return [
	'bootstrap' => Bootstrap::class,
	'actions' => [
		'post_as/usersettings/save' => [],
	],
	'routes' => [
		'collection:post_as:owner' => [
			'path' => '/post_as/owner/{username}',
			'resource' => 'post_as/owner',
			'middleware' => [
				Gatekeeper::class,
			],
		],
	],
];
