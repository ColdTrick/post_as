<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof \ElggEntity) {
	return;
}

$poster_guid = $entity->post_as_actor;
if (empty($poster_guid)) {
	return;
}

$poster = get_entity($poster_guid);
if (!$poster instanceof \ElggUser) {
	return;
}

$poster_link = elgg_view_entity_url($poster);

$brief_description = $poster->getProfileData('briefdescription');
if (!empty($brief_description)) {
	$poster_link .= " - {$brief_description}";
}

echo elgg_format_element('div', ['class' => ['elgg-subtext', 'posted-as-poster']], elgg_echo('post_as:output', [$poster_link]));
