<?php

return [
	
	// generic
	'collection:post_as:owner' => "Posted as content",
	'relationship:can_post_as' => "%s can post on behalf of %s",
	
	// plugin settings
	'post_as:settings:can_edit' => "Allow authorized users to edit existing content",
	'post_as:settings:can_edit:help' => "When enabled autorized users also get edit rights on all the content of the users they're authorized to post as for.",
	
	'post_as:settings:editors:title' => "Editor configuration",
	'post_as:settings:editors:description' => "Below you can select users who will become global editors. These users can post on behalf of everybody on the community.",
	'post_as:settings:editors' => "Find and select editors",
	'post_as:settings:editors:help' => "Search for a user by name or username and select it from the list",
	
	// user plugin settings
	'post_as:usersettings:title' => 'Post As',
	'post_as:usersettings:global_editor' => "A site administrator has authorized you to post on behalf of every community member.",
	'post_as:usersettings:authorized_by' => "The following users have authorized you to post content on their behalf.",
	'post_as:usersettings:description' => "Below you can authorize other members of the site to post content on your behalf.
Be sure to thrust the people you authorize.",
	'post_as:usersettings:authorized_users' => "Authorized members to post on your behalf",
	
	// form
	'post_as:input:label' => "Post as",
	'post_as:myself' => "Myself (%s)",
	
	'post_as:output' => "Posted by: %s",
	
	// posted as
	'post_as:menu:posted_as' => "Posted as",
	'post_as:posted:no_results' => "You haven't created any content on behalf of somebody else",
];
