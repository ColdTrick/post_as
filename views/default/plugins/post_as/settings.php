<?php

/* @var $plugin ElggPlugin */
$plugin = elgg_extract('entity', $vars);

echo elgg_view_field([
	'#type' => 'select',
	'#label' => elgg_echo('post_as:settings:can_edit'),
	'#help' => elgg_echo('post_as:settings:can_edit:help'),
	'name' => 'params[allow_edit]',
	'options_values' => [
		'no' => elgg_echo('option:no'),
		'yes' => elgg_echo('option:yes'),
	],
	'value' => $plugin->allow_edit,
]);
