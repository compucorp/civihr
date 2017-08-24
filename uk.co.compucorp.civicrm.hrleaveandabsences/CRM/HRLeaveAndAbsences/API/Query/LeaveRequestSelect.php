<?php

use CRM_Contact_BAO_Relationship as Relationship;
use CRM_Contact_BAO_RelationshipType as RelationshipType;
use CRM_Hrjobcontract_BAO_HRJobContract as HRJobContract;
use CRM_Hrjobcontract_BAO_HRJobDetails as HRJobDetails;
use CRM_Hrjobcontract_BAO_HRJobContractRevision as HRJobContractRevision;
use CRM_HRLeaveAndAbsences_API_Query_Select as SelectQuery;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

/**
 * This class uses CRM_HRLeaveAndAbsences_API_Query_Select to customize
 * LeaveRequest select queries by automatically joining the LeaveRequest with
 * its LeaveRequestDates and LeaveBalanceChange, allowing us to filter the
 * results based on balance change details, like returning only Public Holiday
 * Leave Requests.
 *
 * It also make sure only valid LeaveRequests (i.e. only those within both an
 * absence period and contract dates) are returned.
 *
 * Finally, it also deals with the security aspect and ensures the current
 * logged in user will see the Leave Requests they have the right to.
 */
class CRM_HRLeaveAndAbsences_API_Query_LeaveRequestSelect {

  use CRM_HRLeaveAndAbsences_Service_SettingsManagerTrait;

  /**
   * @var array
   *   An array of params passed to an API endpoint
   */
  private $params;

  /**
   * @var \CRM_HRLeaveAndAbsences_API_Query_Select
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

    $this->query = new SelectQuery('LeaveRequest', $this->params);
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
    $hasUnassignedAsTrue = isset($this->params['unassigned']) && $this->params['unassigned'] == true;
    $hasUnassignedAsFalse = isset($this->params['unassigned']) && $this->params['unassigned'] == false;

    $conditions = [
      'a.is_deleted = 0',
      'jc.deleted = 0',
      '(
          a.from_date <= jd.period_end_date OR
          jd.period_end_date IS NULL
       )',
      '(
          a.to_date >= jd.period_start_date OR
          (a.to_date IS NULL AND a.from_date >= jd.period_start_date)
        )'
    ];

    if($hasUnassignedAsFalse) {
      $conditions = array_merge($conditions, $this->hasActiveLeaveManagerCondition());
    }

    if($hasUnassignedAsTrue) {
      $conditions[] = "NOT (" . implode(' AND ', $this->hasActiveLeaveManagerCondition()) . ") 
                       OR (r.is_active IS NULL AND rt.is_active IS NULL)";



      $query = "a.contact_id NOT IN(SELECT contact_id_a FROM civicrm_relationship r LEFT JOIN
                       civicrm_relationship_type rt ON rt.id = r.relationship_type_id WHERE
                       ". implode(' AND ', $this->hasActiveLeaveManagerCondition());

      $contactID = $this->getContactIdFromParams();
      if ($contactID) {
        $query .= " AND r.contact_id_a IN(" . implode(',', $contactID) . "))";
      }
      else{
        $query .= ")";
      }

      $conditions[] = $query;
    }

    if(!empty($this->params['managed_by'])) {
      $managerID = (int)$this->params['managed_by'];

      if($hasUnassignedAsFalse) {
        $conditions[] = "r.contact_id_b = {$managerID}";
      }

      if(!isset($this->params['unassigned'])) {
        $activeLeaveManagerCondition = $this->hasActiveLeaveManagerCondition();
        $activeLeaveManagerCondition[] = "r.contact_id_b = {$managerID}";
        $conditions = array_merge($conditions, $activeLeaveManagerCondition);
      }
    }

    if(!empty($this->params['expired'])) {
      $conditions[] = "lbc.expiry_date < '" . date('Y-m-d') . "'";
      $conditions[] = 'lbc.expired_balance_change_id IS NOT NULL';
      $conditions[] = 'lbc.amount < 0';
    }

    if(!empty($this->params['public_holiday'])) {
      $conditions[] = "a.request_type = '" . LeaveRequest::REQUEST_TYPE_PUBLIC_HOLIDAY . "'";
    } else {
      $conditions[] = "a.request_type <> '" . LeaveRequest::REQUEST_TYPE_PUBLIC_HOLIDAY . "'";
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
    $joins = [
      'INNER JOIN ' . LeaveRequestDate::getTableName() . ' lrd ON lrd.leave_request_id = a.id',
      'INNER JOIN ' . HRJobContract::getTableName() . ' jc ON a.contact_id = jc.contact_id',
      'INNER JOIN ' . HRJobContractRevision::getTableName() . ' jcr ON jcr.id = (SELECT id
                    FROM ' . HRJobContractRevision::getTableName() . ' jcr2
                    WHERE
                    jcr2.jobcontract_id = jc.id
                    ORDER BY jcr2.effective_date DESC
                    LIMIT 1)',
      'INNER JOIN ' . HRJobDetails::getTableName() . ' jd ON jd.jobcontract_revision_id = jcr.details_revision_id'
    ];

    if(!empty($this->params['managed_by']) || isset($this->params['unassigned'])) {
      $joins[] = 'LEFT JOIN ' . Relationship::getTableName() . ' r ON r.contact_id_a = a.contact_id';
      $joins[] = 'LEFT JOIN ' . RelationshipType::getTableName() . ' rt ON rt.id = r.relationship_type_id';
    }

    if(!empty($this->params['expired'])) {
      $innerJoin = 'INNER JOIN ' . LeaveBalanceChange::getTableName() . ' lbc';
      $innerJoin .= " ON lbc.source_id = lrd.id AND lbc.source_type = '" . LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY . "'";
      $joins[] =  $innerJoin;
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
    $hasUnassignedAsTrue = isset($this->params['unassigned']) && $this->params['unassigned'] == true;
    $hasManagedBy = isset($this->params['managed_by']);

    if ($hasManagedBy && $hasUnassignedAsTrue) {
      return [];
    }
    $results = $this->query->run();

    if($this->returnFullDetails) {
      $this->addFullDetails($results);
    }

    return $results;
  }

  /**
   * Adds the balance_change and dates to the Leave Requests array returned by
   * query object.
   *
   * This is not the best code in terms of performance, since it will trigger
   * two SQL queries for each returned Leave Request (one to get the balance, and
   * another one to get the dates). But, since we want the query to work just
   * like LeaveRequest.get (including all the params and options) and the query
   * object is not much flexible regarding returning calculated fields (the balance
   * change is the sum of the amount of all balance changes) and related records,
   * this is how it will work for now.
   *
   * @param array $results
   */
  private function addFullDetails(&$results) {
    foreach($results as $i => $leaveRequest) {
      $leaveRequestBao = new LeaveRequest();
      $leaveRequestBao->copyValues($leaveRequest);

      if($this->shouldReturnBalanceChange()) {
        $expiredOnly = !empty($this->params['expired']);
        $balanceChange = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequestBao, $expiredOnly);
        $results[$i]['balance_change'] = $balanceChange;
      }

      if($this->shouldReturnDates()) {
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

  /**
   * Returns if the balance_change field should be included on the returned results
   *
   * @return bool
   */
  private function shouldReturnBalanceChange() {
    return $this->shouldReturnField('balance_change');
  }

  /**
   * Returns if the dates field should be included on the returned results
   *
   * @return bool
   */
  private function shouldReturnDates() {
    return $this->shouldReturnField('dates');
  }

  /**
   * Returns, based on the "return" param, if the given field should be returned
   * on the results.
   *
   * If "return" is empty, it means all the fields should be returned. Otherwise,
   * a field will be returned only if "return" is not empty and it includes the
   * field.
   *
   * @param string $field
   *
   * @return bool
   */
  private function shouldReturnField($field) {
    return empty($this->params['return']) ||
           (is_array($this->params['return']) && in_array($field, $this->params['return']));
  }

  /**
   * Returns the conditions needed to add to the Where clause for
   * contacts that have active leave managers
   *
   * @return array
   */
  private function hasActiveLeaveManagerCondition() {
    $today =  '"' . date('Y-m-d') . '"';
    $leaveApproverRelationshipTypes = $this->getLeaveApproverRelationshipsTypesForWhereIn();

    $conditions = [];
    $conditions[] = 'rt.is_active = 1';
    $conditions[] = 'rt.id IN(' . implode(',', $leaveApproverRelationshipTypes) . ')';
    $conditions[] = 'r.is_active = 1';
    $conditions[] = "(r.start_date IS NULL OR r.start_date <= {$today})";
    $conditions[] = "(r.end_date IS NULL OR r.end_date >= {$today})";

    return $conditions;
  }

  /**
   * Gets the contactID from the params array and
   * returns it as an array.
   *
   * @return array|bool
   */
  private function getContactIdFromParams() {
    $contactID = false;
    $contactPassedAsArray = !empty($this->params['contact_id']['IN']);
    $contactPassedAsID = isset($this->params['contact_id']) && is_numeric($this->params['contact_id']);

    if($contactPassedAsArray) {
      $contactID = $this->params['contact_id']['IN'];
    }

    if($contactPassedAsID) {
      $contactID = [$this->params['contact_id']];
    }

    return $contactID;
  }
}
