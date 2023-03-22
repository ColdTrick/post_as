<?php
/**
 * Show a post as relationship
 *
 * @uses $vars['relationship'] The friendship request relationship
 */

$relationship = elgg_extract('relationship', $vars);
$inverse = (bool) elgg_extract('inverse_relationship', $vars, false);

$entity = get_entity($relationship->guid_two);
if ($inverse) {
	$entity = get_entity($relationship->guid_one);
}

$params = [
	'subtitle' => false,
];
if ($entity instanceof \ElggUser) {
	$params['title'] = elgg_view_entity_url($entity);
	$params['icon_entity'] = $entity;
	$params['icon_size'] = 'tiny';
}

$params = $params + $vars;

echo elgg_view('relationship/elements/summary', $params);
