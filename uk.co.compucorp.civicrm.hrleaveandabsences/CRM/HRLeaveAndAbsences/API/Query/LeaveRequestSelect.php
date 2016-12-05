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

  public function __construct($params) {
    $this->params = $params;
    $this->query = new SelectQuery(LeaveRequest::class, $params, false);
    $this->addJoins();
    $this->addGroupBy();
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
    $this->addDateAndBalanceChangeJoins();
    $this->addContractJoins();
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
   * Joins the Leave Request with Job Contracts, in order to only return
   * the requests which overlap contracts. That is, the request where the start
   * and/or the end date is between the start and end dates of of the contracts
   * of the Leave Request contact.
   */
  private function addContractJoins() {
    $this->query->join(
      'INNER',
      HRJobContract::getTableName(),
      'jc',
      [
        'a.contact_id = jc.contact_id',
        'jc.deleted = 0'
      ]
    );
    $this->query->join(
      'INNER',
      HRJobContractRevision::getTableName(),
      'jcr',
      [
        'jcr.id = (SELECT id
                    FROM ' . HRJobContractRevision::getTableName() . ' jcr2
                    WHERE
                    jcr2.jobcontract_id = jc.id
                    ORDER BY jcr2.effective_date DESC
                    LIMIT 1)'
      ]
    );
    $this->query->join(
      'INNER',
      HRJobDetails::getTableName(),
      'jd',
      [
        'jd.jobcontract_revision_id = jcr.details_revision_id',
        '(
          a.from_date <= jd.period_end_date OR
          jd.period_end_date IS NULL
        )',
        '(
          a.to_date >= jd.period_start_date OR
          (a.to_date IS NULL AND a.from_date >= jd.period_start_date)
        )'
      ]
    );
  }

  /**
   * Joins the Leave Request with its respective LeaveRequestDates and
   * LeaveBalanceChange
   */
  private function addDateAndBalanceChangeJoins() {
    $balanceChangeJoinConditions = [
      'lbc.source_id = lrd.id',
      "lbc.source_type = '" . LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY . "'",
    ];

    $leaveBalanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));
    if (empty($this->params['public_holiday'])) {
      $balanceChangeJoinConditions[] = "lbc.type_id <> {$leaveBalanceChangeTypes['Public Holiday']}";
    }
    else {
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
   * Add a GROUP BY to the query, group the results
   *
   * Since we join with Leave Request Dates and Leave Balance Change, we might
   * end up with multiple records for the same Leave Request. The API infrastructure
   * is smart enough to remove those duplicates once the records are fetched, but
   * this would cause problems with the LIMIT option, as it would be added to
   * the query and the duplicated records would also be included on the limit.
   */
  private function addGroupBy() {
    $groupBy = new CRM_Utils_SQL_Select(LeaveRequest::getTableName() . ' as a');
    $groupBy->groupBy(['a.id']);
    $this->query->merge($groupBy);
  }

}
