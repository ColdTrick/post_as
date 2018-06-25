<?php

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner->canEdit()) {
	return;
}

/* @var $plugin ElggPlugin */
$plugin = elgg_extract('entity', $vars);

echo elgg_view('output/longtext', [
	'value' => elgg_echo('post_as:usersettings:description'),
]);

$authorized = post_as_get_authorized_users($page_owner->guid, true);

echo elgg_view_field([
	'#type' => 'userpicker',
	'#label' => elgg_echo('post_as:usersettings:authorized_users'),
	'name' => 'authorized_users',
	'values' => $authorized,
]);
