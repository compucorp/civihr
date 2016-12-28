<?php

use Civi\API\SelectQuery;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;

/**
 * This class is basically a wrapper around Civi\API\SelectQuery.
 *
 * It's supposed to work just like SelectQuery, but it will automatically join
 * the TOILRequest with its LeaveBalanceChange, allowing us to filter the results
 * based on balance change details, like returning only expired requests.
 */
class CRM_HRLeaveAndAbsences_API_Query_TOILRequestSelect {

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
   * Build the custom query, joining TOILRequests with LeaveBalanceChanges
   */
  private function buildCustomQuery() {
    $customQuery = CRM_Utils_SQL_Select::from(TOILRequest::getTableName() . ' as a');

    $this->addJoins($customQuery);
    $this->addWhere($customQuery);
    $this->addGroupBy($customQuery);

    $this->query = new SelectQuery(TOILRequest::class, $this->params, false);
    $this->query->merge($customQuery);
  }

  /**
   * Add the conditions to the query.
   *
   * If the $params array has the "expired" flag set, the conditions will make
   * sure only expired TOILRequests will be returned
   *
   * @param \CRM_Utils_SQL_Select $customQuery
   */
  private function addWhere(CRM_Utils_SQL_Select $customQuery) {
    $whereClauses = [];

    if(!empty($this->params['expired'])) {
      $whereClauses[] = "lbc.expiry_date < '" . date('Y-m-d') . "'";
      $whereClauses[] = 'lbc.expired_balance_change_id Is NOT NULL';
      $whereClauses[] = 'lbc.amount < 0';
    }

    $customQuery->where($whereClauses);
  }

  /**
   * Add the joins required to join the TOILRequest with its LeaveBalanceChanges.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addJoins(CRM_Utils_SQL_Select $query) {
    $query->join(null, [
      'INNER JOIN ' . LeaveBalanceChange::getTableName() . " lbc 
        ON lbc.source_id = a.id AND lbc.source_type = '" . LeaveBalanceChange::SOURCE_TOIL_REQUEST . "'",
    ]);
  }

  /**
   * Executes the query
   *
   * @return array
   */
  public function run() {
    return $this->query->run();
  }

  /**
   * Add a GROUP BY to the query, group the results
   *
   * Since we join with Leave Balance Change, we might
   * end up with multiple records for the same TOIL Request. The reason is that,
   * once expired, the TOIL Request will be linked to 2 balance changes (the
   * original one and the expired one). The API infrastructure is smart enough
   * to remove those duplicates once the records are fetched, but this would
   * cause problems with the LIMIT option, as it would be added to the query
   * and the duplicated records would also be included on the limit.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addGroupBy(CRM_Utils_SQL_Select $query) {
    $query->groupBy(['a.id']);
  }
}
