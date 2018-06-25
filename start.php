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
	
	// regsiter actions
	elgg_register_action('post_as/usersettings/save', __DIR__ . '/actions/post_as/usersettings/save.php');
}
