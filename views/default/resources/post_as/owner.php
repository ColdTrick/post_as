<?php
/**
 * List all content the user posted as somebody else
 */

use Elgg\EntityNotFoundException;
use Elgg\EntityPermissionsException;

$user = elgg_get_page_owner_entity();
if (!$user instanceof ElggUser) {
	throw new EntityNotFoundException();
}

if (!$user->canEdit() || elgg_get_plugin_setting('allow_edit', 'post_as') !== 'yes') {
	throw new EntityPermissionsException();
}

$config = post_as_get_config();
if (empty($config) || !is_array($config)) {
	throw new EntityPermissionsException();
}

$type_subtype_pairs = [];
foreach ($config as $action => $pair) {
	$type = elgg_extract('type', $pair);
	$subtype = elgg_extract('subtype', $pair);
	
	if (!isset($type_subtype_pairs[$type])) {
		$type_subtype_pairs[$type] = [];
	}
	
	$type_subtype_pairs[$type][] = $subtype;
}

// build page elements
$title = elgg_echo('collection:post_as:owner');

$content = elgg_call(ELGG_IGNORE_ACCESS, function() use ($user, $type_subtype_pairs) {
	return elgg_list_entities([
		'type_subtype_pairs' => $type_subtype_pairs,
		'metadata_name_value_pairs' => [
			'name' => 'post_as_actor',
			'value' => $user->guid,
			'type' => ELGG_VALUE_GUID,
		],
		'no_results' => elgg_echo('post_as:posted:no_results'),
	]);
});

// build page
$page = elgg_view_layout('default', [
	'title' => $title,
	'content' => $content,
	'filter_id' => 'post_as',
	'filter_value' => 'owner',
]);

// draw page
echo elgg_view_page($title, $page);
