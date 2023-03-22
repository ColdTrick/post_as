<?php

namespace ColdTrick\PostAs\Middleware;

use Elgg\Request;

/**
 * PostAs Middleware
 */
class PostAs {
	
	protected int $entity_guid;
	
	/**
	 * Check if a special access suffix is needed for this route
	 *
	 * @param Request $request The current request
	 *
	 * @return void
	 */
	public function __invoke(Request $request): void {
		$user = elgg_get_logged_in_user_entity();
		if (!$user instanceof \ElggUser || $user->isAdmin()) {
			// no user, or admin which already has access
			return;
		}
		
		$entity = elgg_call(ELGG_IGNORE_ACCESS, function() use ($request) {
			return $request->getEntityParam();
		});
		if (!$entity instanceof \ElggEntity) {
			return;
		}
		
		if (!post_as_is_authorized($entity->owner_guid, $user->guid)) {
			// make sure the user is a post_as user (this will prevent group owners from accessing private group data)
			return;
		}
		
		if (!$entity->canEdit($user->guid)) {
			return;
		}
		
		$this->entity_guid = $entity->guid;
		
		$request->elgg()->events->registerHandler('get_sql', 'access', [$this, 'addSqlSuffix']);
	}
	
	/**
	 * Add sql suffix so the current user is allowed to fetch (private) entities
	 *
	 * @param \Elgg\Event $event 'get_sql', 'access'
	 *
	 * @return null|array
	 */
	public function addSqlSuffix(\Elgg\Event $event): ?array {
		$guid_column = $event->getParam('guid_column');
		if (empty($guid_column) || $event->getParam('ignore_access')) {
			return null;
		}
		
		$table_alias = $event->getParam('table_alias');
		$table_alias = $table_alias ? "{$table_alias}." : '';
		
		$clauses = $event->getValue();
		$clauses['ors'] = elgg_extract('ors', $clauses, []);
		
		$clauses['ors']['post_as'] = "{$table_alias}{$guid_column} = {$this->entity_guid}";
		
		return $clauses;
	}
}
