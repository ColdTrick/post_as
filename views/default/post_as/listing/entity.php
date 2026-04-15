<?php
/**
 * Show an entity in the post as listing
 *
 * This is needed to enforce access when drawing the entity
 *
 * @uses $vars['entity'] the entity to show
 */

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggEntity) {
	return;
}

unset($vars['item_view']);

echo elgg_call(ELGG_ENFORCE_ACCESS, function() use ($entity, $vars) {
	return elgg_view_entity($entity, $vars);
});
