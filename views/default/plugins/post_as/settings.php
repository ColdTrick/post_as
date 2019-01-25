<?php

/* @var $plugin ElggPlugin */
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
