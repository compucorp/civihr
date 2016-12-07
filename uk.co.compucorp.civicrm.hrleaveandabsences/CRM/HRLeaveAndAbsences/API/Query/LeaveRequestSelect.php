<?php

use Civi\API\SelectQuery;
use CRM_Hrjobcontract_BAO_HRJobContract as HRJobContract;
use CRM_Hrjobcontract_BAO_HRJobDetails as HRJobDetails;
use CRM_Hrjobcontract_BAO_HRJobContractRevision as HRJobContractRevision;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

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

  /**
   * @var bool
   *   A flag indicating if the returned Leave Requests will also include the
   *   balance change and the Leave Request dates
   */
  private $returnFullDetails = false;

  public function __construct($params) {
    $this->params = $params;
    $this->buildCustomQuery();
  }

  /**
   * Build the custom query, joining LeaveRequests with LeaveRequestDates,
   * LeaveBalanceChanges, HRJobContract and HRJobDetails.
   *
   * It also add conditions in order to only return the LeaveRequests overlapping
   * a contract and, in case the 'public_holiday' param is set, it only returns
   * Public Holiday Leave Requests.
   */
  private function buildCustomQuery() {
    $customQuery = CRM_Utils_SQL_Select::from(LeaveRequest::getTableName() . ' as a');

    $this->addJoins($customQuery);
    $this->addWhere($customQuery);
    $this->addGroupBy($customQuery);

    $this->query = new SelectQuery(LeaveRequest::class, $this->params, false);
    $this->query->merge($customQuery);
  }

  /**
   * Add the conditions to the query.
   *
   * This where we make sure we only return Leave Requests overlapping non
   * deleted contracts and with balance changes
   *
   * @param \CRM_Utils_SQL_Select $customQuery
   */
  private function addWhere(CRM_Utils_SQL_Select $customQuery) {
    $customQuery->where([
      'jc.deleted = 0',
      '(
          a.from_date <= jd.period_end_date OR
          jd.period_end_date IS NULL
       )',
      '(
          a.to_date >= jd.period_start_date OR
          (a.to_date IS NULL AND a.from_date >= jd.period_start_date)
        )',
      'lbc.source_id = lrd.id',
      "lbc.source_type = '" . LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY . "'",
    ]);
  }

  /**
   * Add the joins required to join LeaveRequest with LeaveRequestDate and then
   * with LeaveBalanceChange.
   *
   * If the $params array has the public_holiday flag set and it's true, the
   * join condition will make sure only LeaveRequests linked to a LeaveBalanceChange
   * of the Public Holiday type will be returned.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addJoins(CRM_Utils_SQL_Select $query) {
    $leaveBalanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));

    $balanceChangeJoinCondition = "lbc.type_id <> {$leaveBalanceChangeTypes['Public Holiday']}";
    if (!empty($this->params['public_holiday'])) {
      $balanceChangeJoinCondition = "lbc.type_id = {$leaveBalanceChangeTypes['Public Holiday']}";
    }

    $query->join(null, [
      'INNER JOIN ' . LeaveRequestDate::getTableName() . ' lrd ON lrd.leave_request_id = a.id',
      'INNER JOIN ' . LeaveBalanceChange::getTableName() . ' lbc ON ' . $balanceChangeJoinCondition,
      'INNER JOIN ' . HRJobContract::getTableName() . ' jc ON a.contact_id = jc.contact_id',
      'INNER JOIN ' . HRJobContractRevision::getTableName() . ' jcr ON jcr.id = (SELECT id
                    FROM ' . HRJobContractRevision::getTableName() . ' jcr2
                    WHERE
                    jcr2.jobcontract_id = jc.id
                    ORDER BY jcr2.effective_date DESC
                    LIMIT 1)',
      'INNER JOIN ' . HRJobDetails::getTableName() . ' jd ON jd.jobcontract_revision_id = jcr.details_revision_id'
    ]);
  }

  /**
   * Sets if this query should return the Leave Requests with full details. That
   * is, with its balance change and its dates
   *
   * @param boolean $value
   */
  public function setReturnFullDetails($value) {
    $this->returnFullDetails = $value;
  }

  /**
   * Executes the query
   *
   * @return array
   */
  public function run() {
    $results = $this->query->run();

    if($this->returnFullDetails) {
      $this->addFullDetails($results);
    }

    return $results;
  }

  /**
   * Adds the balance_change and dates to the Leave Requests array returned by
   * the SelectQuery.
   *
   * This is not the best code in terms of performance, since it will trigger
   * two SQL queries for each returned Leave Request (one to get the balance, and
   * another one to get the dates). But, since we want the query to work just
   * like LeaveRequest.get (including all the params and options) and the SelectQuery
   * class is not much flexible regarding returning calculated fields (the balance
   * change is the sum of the amount of all balance changes) and related records,
   * this is how it will work for now.
   *
   * @param array $results
   */
  private function addFullDetails(&$results) {
    foreach($results as $i => $leaveRequest) {
      $leaveRequestBao = new LeaveRequest();
      $leaveRequestBao->copyValues($leaveRequest);

      $balanceChange = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequestBao);
      $results[$i]['balance_change'] = $balanceChange;

      $dates = $leaveRequestBao->getDates();
      $results[$i]['dates'] = [];
      foreach($dates as $date) {
        $results[$i]['dates'][] = [
          'id' => $date->id,
          'date' => $date->date
        ];
      }
    }
  }

  /**
   * Add a GROUP BY to the query, group the results
   *
   * Since we join with Leave Request Dates and Leave Balance Change, we might
   * end up with multiple records for the same Leave Request. The API infrastructure
   * is smart enough to remove those duplicates once the records are fetched, but
   * this would cause problems with the LIMIT option, as it would be added to
   * the query and the duplicated records would also be included on the limit.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addGroupBy(CRM_Utils_SQL_Select $query) {
    $query->groupBy(['a.id']);
  }

}
