<?php

$plugin_id = get_input('plugin_id');
$user_guid = (int) get_input('user_guid', elgg_get_logged_in_user_guid());
if (empty($plugin_id) || empty($user_guid)) {
	return elgg_error_response(elgg_echo('error:missing_data'));
}

$plugin = elgg_get_plugin_from_id($plugin_id);
$plugin_name = $plugin->getDisplayName();

$user = get_user($user_guid);

$authorized_users = (array) get_input('authorized_users');
array_walk($authorized_users, function(&$value) {
	$value = (int) $value;
});
$authorized_users = array_filter($authorized_users, function ($value) {
	return (int) $value > 0;
});

if (empty($authorized_users)) {
	remove_entity_relationships($user->guid, POST_AS_RELATIONSHIP, true);
	
	return elgg_ok_response('', elgg_echo('plugins:usersettings:save:ok', [$plugin_name]));
}

$current_authorized = post_as_get_authorized_users($user->guid, true);

$new_authorized = array_diff($authorized_users, $current_authorized);
if (!empty($new_authorized)) {
	foreach ($new_authorized as $authorized_guid) {
		add_entity_relationship($authorized_guid, POST_AS_RELATIONSHIP, $user->guid);
	}
}

$removed_authorized = array_diff($current_authorized, $authorized_users);
if (!empty($removed_authorized)) {
	foreach ($removed_authorized as $no_longer_authorized) {
		remove_entity_relationship($no_longer_authorized, POST_AS_RELATIONSHIP, $user->guid);
	}
}

return elgg_ok_response('', elgg_echo('plugins:usersettings:save:ok', [$plugin_name]));
