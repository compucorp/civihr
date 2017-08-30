<?php

use CRM_Contact_BAO_Contact as Contact;
use CRM_Contact_BAO_Relationship as Relationship;
use CRM_Contact_BAO_RelationshipType as RelationshipType;
use CRM_Hrjobcontract_BAO_HRJobContract as HRJobContract;
use CRM_Hrjobcontract_BAO_HRJobDetails as HRJobDetails;
use CRM_Hrjobcontract_BAO_HRJobContractRevision as HRJobContractRevision;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeavePeriodEntitlement as LeavePeriodEntitlement;
use CRM_HRLeaveAndAbsences_API_Query_Select as SelectQuery;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * This is the Query class behind the LeavePeriodEntitlement.getLeaveBalances
 * API.
 *
 * It encapsulates a SelectQuery object that does queries on the contact table.
 * After the results are fetched, it queries the Leave Balances for all the
 * returned Contacts and format everything in the format expected by the API.
 */
class CRM_HRLeaveAndAbsences_API_Query_LeaveBalancesSelect {

  use CRM_HRLeaveAndAbsences_Service_SettingsManagerTrait;

  /**
   * @var CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $absencePeriod;

  /**
   * @var CRM_HRLeaveAndAbsences_Service_LeaveManager
   */
  private $leaveManagerService;

  /**
   * CRM_HRLeaveAndAbsences_API_Query_LeaveBalancesSelect constructor.
   *
   * @param array $params
   * @param CRM_HRLeaveAndAbsences_Service_LeaveManager $leaveManagerService
   */
  public function __construct($params, LeaveManagerService $leaveManagerService) {
    $this->params = $params;
    $this->leaveManagerService = $leaveManagerService;
    $this->buildCustomQuery();
  }

  /**
   * Builds the custom query (add joins, where clauses, set params etc)
   */
  private function buildCustomQuery() {
    $customQuery = CRM_Utils_SQL_Select::from(Contact::getTableName() . ' as a');

    $this->addJoins($customQuery);
    $this->addWhere($customQuery);
    $this->setReturnFields();

    $this->query = new SelectQuery('Contact', $this->params);
    $this->query->merge($customQuery);
  }

  /**
   * Adds all the JOINs necessary for this query.
   *
   * @param CRM_Utils_SQL_Select $query
   */
  private function addJoins(CRM_Utils_SQL_Select $query) {
    $absencePeriod = $this->getAbsencePeriod();

    $joins = [
      'INNER JOIN ' . HRJobContract::getTableName() . ' jc ON a.id = jc.contact_id',
      'INNER JOIN ' . HRJobContractRevision::getTableName() . ' jcr ON jcr.id = (SELECT id
                    FROM ' . HRJobContractRevision::getTableName() . " jcr2
                    WHERE
                    jcr2.jobcontract_id = jc.id AND (
                      jcr2.effective_date <= '{$absencePeriod->start_date}'
                      OR
                      (
                        jcr2.effective_date >= '{$absencePeriod->start_date}' AND
                        jcr2.effective_date <= '{$absencePeriod->end_date}' 
                      )
                    )
                    ORDER BY jcr2.effective_date DESC, jcr2.id DESC
                    LIMIT 1)",
      'INNER JOIN ' . HRJobDetails::getTableName() . ' jd ON jd.jobcontract_revision_id = jcr.details_revision_id'
    ];

    if($this->getManagerID()) {
      $joins[] = 'LEFT JOIN ' . Relationship::getTableName() . ' r ON r.contact_id_a = a.id';
      $joins[] = 'LEFT JOIN ' . RelationshipType::getTableName() . ' rt ON rt.id = r.relationship_type_id';
    }

    $query->join(null, $joins);
  }

  /**
   * Add all the where clauses of the query.
   *
   * This method works in combination with addJoins() (that is, it references
   * tables added by the join clauses).
   *
   * @param CRM_Utils_SQL_Select $customQuery
   */
  private function addWhere(CRM_Utils_SQL_Select $customQuery) {
    $absencePeriod = $this->getAbsencePeriod();

    $conditions = [
      'a.is_deleted = 0',
      'jc.deleted = 0',
      "(
        (jd.period_end_date IS NOT NULL AND jd.period_start_date <= '{$absencePeriod->end_date}' AND jd.period_end_date >= '{$absencePeriod->start_date}')
          OR
        (jd.period_end_date IS NULL AND 
          (
            (jd.period_start_date >= '{$absencePeriod->start_date}' AND jd.period_start_date <= '{$absencePeriod->end_date}')
            OR
            jd.period_start_date <= '{$absencePeriod->end_date}'
          )
        )
      )"
    ];

    $managerID = $this->getManagerID();
    if($managerID) {
      $activeLeaveManagerCondition = $this->hasActiveLeaveManagerCondition();
      $activeLeaveManagerCondition[] = "r.contact_id_b = {$managerID}";
      $conditions = array_merge($conditions, $activeLeaveManagerCondition);
    }

    $customQuery->where($conditions);
  }

  /**
   * Runs the query.
   *
   * The query works in 2 steps:
   * 1. Get all the Contacts matching the criteria passed to the API
   * 2. For each contacts, get their Leave Balances and add it to the returned
   * result
   *
   * This method also has a shortcut for is_count queries. When the client only
   * wants the number of records, the second step isn't necessary and won't be
   * executed.
   *
   * @return array
   */
  public function run() {
    $results = $this->query->run();

    if($this->isCount()) {
      return $results;
    }

    $leaveBalances = $this->getLeaveBalances($results);

    return $leaveBalances;
  }

  /**
   * Whether this is a count query or not
   *
   * @return bool
   */
  private function isCount() {
    return !empty($this->params['options']['is_count']);
  }

  /**
   * Gets the Leave Balances for the contacts in the given array.
   *
   * @param array $contacts
   *
   * @return array
   */
  private function getLeaveBalances($contacts) {
    $contactIDs = array_column($contacts, 'id');

    $absenceTypeID = isset($this->params['type_id']) ? $this->params['type_id'] : null;
    $absencePeriodID = $this->params['period_id'];

    $entitlements = LeavePeriodEntitlement::getEntitlementsForContacts(
      $contactIDs,
      $absencePeriodID,
      $absenceTypeID
    );
    $balances = LeaveBalanceChange::getBalanceForContacts(
      $contactIDs,
      $absencePeriodID,
      $absenceTypeID
    );
    $requestedBalances = LeaveBalanceChange::getOpenLeaveRequestBalanceForContacts(
      $contactIDs,
      $absencePeriodID,
      $absenceTypeID
    );

    $newResults = [];
    $payload = [];
    foreach ($entitlements as $contactID => $contactEntitlements) {
      foreach ($contactEntitlements as $absenceTypeID => $entitlement) {
        $balance = isset($balances[$contactID][$absenceTypeID]) ? $balances[$contactID][$absenceTypeID] : 0;
        $requested = isset($requestedBalances[$contactID][$absenceTypeID]) ? $requestedBalances[$contactID][$absenceTypeID] : 0;
        $used = $entitlement - $balance;

        $payload[$contactID][] = [
          'id' => $absenceTypeID,
          'entitlement' => $entitlement,
          'used' => $used,
          'balance' => abs($balance),
          'requested' => abs($requested)
        ];
      }
    }

    foreach ($contacts as $i => $result) {
      $newResults[$i] = [
        'contact_id' => $result['id'],
        'contact_display_name' => $result['display_name'],
        'absence_types' => $payload[$result['id']]
      ];
    }

    return $newResults;
  }

  /**
   * Returns the where clauses that will be added to the query to make sure that
   * managers can only see their managees.
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
   * Returns an instance of the AbsencePeriod BAO, representing the period with
   * the id passed via the period_id param.
   *
   * @return \CRM_HRLeaveAndAbsences_BAO_AbsencePeriod|object
   */
  private function getAbsencePeriod() {
    if(!$this->absencePeriod) {
      $this->absencePeriod = AbsencePeriod::findById($this->params['period_id']);
    }

    return $this->absencePeriod;
  }

  /**
   * Returns the Manager ID that will be used by the query.
   *
   * If the current user is an admin, they can see all the contacts. In that
   * case the ID will be null by default or the value passed to the managed_by
   * param. Other users can only see their managees, so the ID will always be
   * the ID of the current logged in user, regardless of the value passed to the
   * managed_by param.
   *
   * @return int|mixed|null
   */
  private function getManagerID() {
    $managerID = null;

    if(!$this->leaveManagerService->currentUserIsAdmin()) {
      $managerID = (int)CRM_Core_Session::getLoggedInContactID();
    } elseif(!empty($this->params['managed_by'])) {
      $managerID = $this->params['managed_by'];
    }

    return $managerID;
  }

  /**
   * Sets the fields that should be returned by the main Contact query.
   */
  private function setReturnFields() {
    $this->params['return'] = ['id', 'display_name'];
  }

}
