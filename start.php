<?php
/**
 * Main plugin file
 */

define('POST_AS_RELATIONSHIP', 'can_post_as');

require_once(__DIR__ . '/lib/functions.php');

// register default Elgg events
elgg_register_event_handler('init', 'system', 'post_as_init');

/**
 * Called during system init
 *
 * @return void
 */
function post_as_init() {
	
	// extend views
	elgg_extend_view('forms/blog/save', 'post_as/input');
	elgg_register_plugin_hook_handler('action', 'blog/save', '\ColdTrick\PostAs\SaveAction::prepareAction');
	elgg_extend_view('forms/static/edit', 'post_as/input');
	elgg_register_plugin_hook_handler('action', 'static/edit', '\ColdTrick\PostAs\SaveAction::prepareAction');
	
	// plugin hooks
	elgg_register_plugin_hook_handler('permissions_check', 'object', '\ColdTrick\PostAs\Permissions::canEdit');
	
	// regsiter actions
	elgg_register_action('post_as/usersettings/save', __DIR__ . '/actions/post_as/usersettings/save.php');
}
