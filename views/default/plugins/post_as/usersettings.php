<?php

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner->canEdit()) {
	return;
}

/* @var $plugin \ElggPlugin */
$plugin = elgg_extract('entity', $vars);

// other users who have authorized this user
if (post_as_is_global_editor($page_owner->guid)) {
	echo elgg_view_message('info', elgg_echo('post_as:usersettings:global_editor'));
} else {
	$list = elgg_list_relationships([
		'type' => 'user',
		'limit' => false,
		'relationship' => POST_AS_RELATIONSHIP,
		'relationship_guid' => $page_owner->guid,
	]);
	if (!empty($list)) {
		echo elgg_view('output/longtext', [
			'value' => elgg_echo('post_as:usersettings:authorized_by'),
		]);
		
		echo $list;
	}
}

// allow this user to authorize others
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
