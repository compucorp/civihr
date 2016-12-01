<?php

use \Civi\API\SelectQuery;
use \CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use \CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use \CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

/**
 * This class is basically a wrapper around Civi\API\SelectQuery.
 *
 * It's supposed to work just like SelectQuery, but it will automatically join
 * the LeaveRequest with its LeaveRequestDates and LeaveBalanceChange, allowing
 * us to filter the results based on balance change details, like returning only
 * Public Holiday Leave Requests.
 */
class CRM_HRLeaveAndAbsences_API_Query_LeaveRequestSelect {

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
    $this->query = new SelectQuery(LeaveRequest::class, $params, false);
    $this->addJoins();
  }

  /**
   * Add the joins required to join LeaveRequest with LeaveRequestDate and then
   * with LeaveBalanceChange.
   *
   * If the $params array has the public_holiday flag set and it's true, the
   * join condition will make sure only LeaveRequests linked to a LeaveBalanceChange
   * of the Public Holiday type will be returned.
   */
  private function addJoins() {
    $balanceChangeJoinConditions = [
      'lbc.source_id = lrd.id',
      "lbc.source_type = '" . LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY . "'",
    ];

    $leaveBalanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));
    if(empty($this->params['public_holiday'])) {
      $balanceChangeJoinConditions[] = "lbc.type_id <> {$leaveBalanceChangeTypes['Public Holiday']}";
    } else {
      $balanceChangeJoinConditions[] = "lbc.type_id = {$leaveBalanceChangeTypes['Public Holiday']}";
    }

    $this->query->join(
      'INNER',
      LeaveRequestDate::getTableName(),
      'lrd',
      ['lrd.leave_request_id = a.id']
    );
    $this->query->join(
      'INNER',
      LeaveBalanceChange::getTableName(),
      'lbc',
      $balanceChangeJoinConditions
    );
  }

  /**
   * Executes the query
   *
   * @return array
   */
  public function run() {
    return $this->query->run();
  }

}
