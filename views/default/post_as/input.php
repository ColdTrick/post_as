<?php
/**
 * Extend this view to a form for which you wish to support post as
 */

$show_on_edit = (bool) elgg_extract('show_on_edit', $vars, false);
if (!$show_on_edit && ((isset($vars['entity']) && !empty($vars['entity'])) || (isset($vars['guid']) && !empty($vars['guid'])))) {
	// probably edit form, not supported
	return;
}

$global_editor = post_as_is_global_editor();
$owners = post_as_get_posters();
if (!$global_editor && empty($owners)) {
	// not authorized for any users
	return;
}

$user = elgg_get_logged_in_user_entity();

if (!$global_editor) {
	// present a limited list of users
	$options_values = [
		$user->guid => elgg_echo('post_as:myself', [$user->getDisplayName()]),
	];
	
	/* @var $owner ElggUser */
	foreach ($owners as $owner) {
		$options_values[$owner->guid] = $owner->getDisplayName();
	}
	
	echo elgg_view_field([
		'#type' => 'select',
		'#label' => elgg_echo('post_as:input:label'),
		'name' => 'post_as_owner_guid',
		'value' => elgg_get_sticky_value('post_as', 'post_as_owner_guid', $user->guid),
		'options_values' => $options_values,
	]);
} else {
	// global editor gets to post as anybody
	echo elgg_view_field([
		'#type' => 'userpicker',
		'#label' => elgg_echo('post_as:input:label'),
		'name' => 'post_as_owner_guid',
		'value' => elgg_get_sticky_value('post_as', 'post_as_owner_guid', $user->guid),
		'limit' => 1,
	]);
}

// clear sticky form
elgg_clear_sticky_form('post_as');
