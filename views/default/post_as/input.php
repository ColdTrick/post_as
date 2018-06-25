<?php
/**
 * Extend this view to a form for which you wish to support post as
 */

$owners = post_as_get_posters();
if (empty($owners)) {
	// not authorized for any users
	return;
}

if (isset($vars['entity']) || isset($vars['guid'])) {
	// probably edit form, not supported
	return;
}

$user = elgg_get_logged_in_user_entity();

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
	'value' => $user->guid,
	'options_values' => $options_values,
]);