<?php

use ColdTrick\PostAs\Bootstrap;
use Elgg\Router\Middleware\UserPageOwnerCanEditGatekeeper;

if (!defined('POST_AS_RELATIONSHIP')) {
	define('POST_AS_RELATIONSHIP', 'can_post_as');
}

require_once(__DIR__ . '/lib/functions.php');

return [
	'plugin' => [
		'version' => '5.0.3',
		'dependencies' => [
			'blog' => [
				'must_be_active' => false,
				'position' => 'after',
			],
		],
	],
	'bootstrap' => Bootstrap::class,
	'actions' => [
		'post_as/usersettings/save' => [],
	],
	'events' => [
		'container_permissions_check' => [
			'all' => [
				'\ColdTrick\PostAs\Permissions::canWriteToContainer' => [],
			],
		],
		'permissions_check' => [
			'all' => [
				'\ColdTrick\PostAs\Permissions::canEdit' => [],
			],
		],
		'register' => [
			'menu:owner_block' => [
				'\ColdTrick\PostAs\Menus\OwnerBlock::addPostedAs' => [],
			],
		],
		'setting' => [
			'plugin' => [
				'\ColdTrick\PostAs\PluginSettings::convertArrayToString' => [],
			],
		],
	],
	'routes' => [
		'collection:post_as:owner' => [
			'path' => '/post_as/owner/{username}',
			'resource' => 'post_as/owner',
			'middleware' => [
				UserPageOwnerCanEditGatekeeper::class,
			],
		],
	],
];
