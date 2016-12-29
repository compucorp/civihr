<?php

use Civi\API\SelectQuery;
use CRM_Contact_BAO_Relationship as Relationship;
use CRM_Contact_BAO_RelationshipType as RelationshipType;
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
   * Build the custom query. It joins LeaveRequests with LeaveRequestDates,
   * LeaveBalanceChanges, HRJobContract and HRJobDetails and also with
   * any necessary additional tables, depending on the given $params
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
   * deleted contracts and with balance changes.
   *
   * This is also were we add some additional conditions, depending on filter/flags
   * passed to the $params array
   *
   * @param \CRM_Utils_SQL_Select $customQuery
   */
  private function addWhere(CRM_Utils_SQL_Select $customQuery) {
    $conditions = [
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
    ];

    if(!empty($this->params['managed_by'])) {
      $managerID = (int)$this->params['managed_by'];
      $today =  '"' . date('Y-m-d') . '"';

      $conditions[] = 'rt.is_active = 1';
      $conditions[] = 'rt.name_a_b = "has Leave Approved by"';
      $conditions[] = 'r.is_active = 1';
      $conditions[] = "r.contact_id_b = {$managerID}";
      $conditions[] = "(r.start_date IS NULL OR r.start_date <= {$today})";
      $conditions[] = "(r.end_date IS NULL OR r.end_date >= {$today})";
    }

    $customQuery->where($conditions);
  }

  /**
   * Add the joins required to join LeaveRequest with LeaveRequestDate and then
   * with LeaveBalanceChange.
   *
   * If the $params array has the public_holiday flag set and it's true, the
   * join condition will make sure only LeaveRequests linked to a LeaveBalanceChange
   * of the Public Holiday type will be returned.
   *
   * If the $params array has the managed_by flag set, the join condition will
   * also include joins with the civicrm_relationship and civicrm_relationship_type
   * tables.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addJoins(CRM_Utils_SQL_Select $query) {
    $leaveBalanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id'));

    $balanceChangeJoinCondition = "lbc.type_id <> {$leaveBalanceChangeTypes['Public Holiday']}";
    if (!empty($this->params['public_holiday'])) {
      $balanceChangeJoinCondition = "lbc.type_id = {$leaveBalanceChangeTypes['Public Holiday']}";
    }

    $joins = [
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
    ];

    if(!empty($this->params['managed_by'])) {
      $joins[] = 'INNER JOIN ' . Relationship::getTableName() . ' r ON r.contact_id_a = a.contact_id';
      $joins[] = 'INNER JOIN ' . RelationshipType::getTableName() . ' rt ON rt.id = r.relationship_type_id';
    }

    $query->join(null, $joins);
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
