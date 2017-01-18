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
}
