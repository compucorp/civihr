<?php

use Civi\API\SelectQuery;
use CRM_HRComments_BAO_Comment as Comment;

/**
 * This class is basically a wrapper around Civi\API\SelectQuery.
 */
class CRM_HRComments_API_Query_CommentSelect {

  /**
   * @var array
   *   An array of params passed to an API endpoint
   */
  private $params;

  /**
   * @var \Civi\API\SelectQuery
   *  The SelectQuery instance wrapped by this class
   */
  private $query;

  public function __construct($params) {
    $this->params = $params;
    $this->buildCustomQuery();
  }

  /**
   * Build the custom query.
   */
  private function buildCustomQuery() {
    $customQuery = CRM_Utils_SQL_Select::from(Comment::getTableName() . ' as a');

    $this->addWhere($customQuery);
    $this->filterReturnFields();
    $this->query = new SelectQuery(Comment::class, $this->params, false);
    $this->query->merge($customQuery);
  }

  /**
   * Add the conditions to the query.
   *
   * This where it is ensured that only non soft-deleted comments are returned
   *
   * @param \CRM_Utils_SQL_Select $customQuery
   */
  private function addWhere(CRM_Utils_SQL_Select $customQuery) {
    $conditions = $this->getACLConditions();

    $conditions[] = 'a.is_deleted = 0';
    $customQuery->where($conditions);
  }

  /**
   * Executes the query
   *
   * @return array
   */
  public function run() {
    $results = $this->query->run();
    return $results;
  }

  /**
   * This function allows some fields to be filtered out of the query results.
   * Currently only the is_deleted field is filtered out
   */
  private function filterReturnFields() {
    if (empty($this->params['return'])) {
      $allFields = array_keys(Comment::fields());
      $key = array_search('is_deleted', $allFields);
      unset($allFields[$key]);
      $this->params['return'] = $allFields;
    }

    if (!empty($this->params['return'])) {
      if (in_array('is_deleted', $this->params['return'])){
        $key = array_search('is_deleted', $this->params['return']);
        unset($this->params['return'][$key]);
      }
    }
  }

  /**
   * Returns an array of ACL conditions to be added to the query
   *
   * @return array
   *   An array in this format:
   *   [
   *      "field == 'foo'",
   *      "(other_field >= 10 OR other_field IS NULL)"
   *   ]
   */
  private function getACLConditions() {
    return $this->invokeSelectWhereClauseHook();
  }

  /**
   * This triggers/declares a custom hook called hook_hrcomments_selectWhereClause.
   *
   * This hook works in a similar way to hook_civicrm_selectWhereClause, but is
   * makes the params array available to the implementations. This way, it's
   * possible to have conditional ACLs based on things like the entity_name (
   * For example, we can have ACLs applied only to comments linked to a Leave
   * Request).
   *
   * The hook accepts 2 params:
   * - $conditions: an array of conditions that will be added to the query. This
   * param is expected to be received as a reference, so it can be modified by
   * other hooks
   * - $params: an array of params passed to the query. Ideally, this should not
   * be changed, but in case that happens, the query will not be affected and
   * the query will work with the original params.
   *
   * @return array
   */
  private function invokeSelectWhereClauseHook() {
    $conditions = [];

    // make a copy of $params to avoid having them modified by the hook
    // implementations
    $params = $this->params;
    CRM_Utils_Hook::singleton()->invoke(2, $conditions,
      $params,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      CRM_Utils_Hook::$_nullObject,
      'hrcomments_selectWhereClause'
    );

    return $conditions;
  }
}
