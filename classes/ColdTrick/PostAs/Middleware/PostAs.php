<?php

namespace ColdTrick\PostAs\Middleware;

use Elgg\Request;

class PostAs {
	
	/**
	 * @var int
	 */
	private $entity_guid;
	
	/**
	 * Check if a special access suffix is needed for this route
	 *
	 * @param Request $request The current request
	 *
	 * @return void
	 */
	public function __invoke(Request $request) {
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
		
		if (!$entity->canEdit($user->guid)) {
			return;
		}
		
		$this->entity_guid = $entity->guid;
		
		$request->elgg()->hooks->registerHandler('get_sql', 'access', [$this, 'addSqlSuffix']);
	}
	
	/**
	 * Add sql suffix so the current user is allowed to fetch (private) entities
	 *
	 * @param \Elgg\Hook $hook 'get_sql', 'access'
	 *
	 * @return void|array
	 */
	public function addSqlSuffix(\Elgg\Hook $hook) {
		
		$guid_column = $hook->getParam('guid_column');
		if (empty($guid_column) || $hook->getParam('ignore_access')) {
			return;
		}
		
		$table_alias = $hook->getParam('table_alias');
		$table_alias = $table_alias ? "{$table_alias}." : '';
		
		$clauses = $hook->getValue();
		$clauses['ors'] = elgg_extract('ors', $clauses, []);
		
		$clauses['ors']['post_as'] = "{$table_alias}{$guid_column} = {$this->entity_guid}";
		
		return $clauses;
	}
}
