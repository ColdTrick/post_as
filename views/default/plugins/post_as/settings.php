<?php

/* @var $plugin \ElggPlugin */
$plugin = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'checkbox',
	'#label' => elgg_echo('post_as:settings:can_edit'),
	'#help' => elgg_echo('post_as:settings:can_edit:help'),
	'name' => 'params[allow_edit]',
	'default' => 'no',
	'value' => 'yes',
	'checked' => $plugin->allow_edit === 'yes',
	'switch' => 1,
]);

// editors
$editors = elgg_view('output/longtext', [
	'value' => elgg_echo('post_as:settings:editors:description'),
]);

$editors .= elgg_view_field([
	'#type' => 'userpicker',
	'#label' => elgg_echo('post_as:settings:editors'),
	'#help' => elgg_echo('post_as:settings:editors:help'),
	'name' => 'params[editor_guids]',
	'values' => $plugin->editor_guids ? elgg_string_to_array((string) $plugin->editor_guids) : null,
	'show_friends' => false,
]);

echo elgg_view_module('info', elgg_echo('post_as:settings:editors:title'), $editors);
