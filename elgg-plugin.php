<?php

use ColdTrick\PostAs\Bootstrap;

define('POST_AS_RELATIONSHIP', 'can_post_as');

require_once(__DIR__ . '/lib/functions.php');

return [
	'bootstrap' => Bootstrap::class,
	'actions' => [
		'post_as/usersettings/save' => [],
	],
];
