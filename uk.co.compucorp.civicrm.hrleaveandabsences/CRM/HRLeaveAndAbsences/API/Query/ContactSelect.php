<?php

use CRM_Contact_BAO_Relationship as Relationship;
use CRM_Contact_BAO_RelationshipType as RelationshipType;
use CRM_Contact_BAO_Contact as Contact;
use CRM_HRLeaveAndAbsences_API_Query_Select as SelectQuery;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * Uses CRM_HRLeaveAndAbsences_API_Query_Select to tweak select queries to
 * Contacts and inject custom conditions to it
 */
class CRM_HRLeaveAndAbsences_API_Query_ContactSelect {

  use CRM_HRLeaveAndAbsences_Service_SettingsManagerTrait;

  /**
   * @var array
   *   An array of params passed to an API endpoint
   */
  private $params;

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveManager
   */
  private $leaveManagerService;

  /**
   * @var \CRM_HRLeaveAndAbsences_API_Query_Select
   *  The SelectQuery instance wrapped by this class
   */
  private $query;

  /**
   * CRM_HRLeaveAndAbsences_API_Query_ContactSelect constructor.
   *
   * @param array $params
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveManager $leaveManagerService
   */
  public function __construct($params, LeaveManagerService $leaveManagerService) {
    $this->params = $params;
    $this->leaveManagerService = $leaveManagerService;
    $this->buildCustomQuery();
  }

  /**
   * Build the custom query.
   */
  private function buildCustomQuery() {
    $customQuery = CRM_Utils_SQL_Select::from(Contact::getTableName() . ' as a');

    $this->addJoins($customQuery);
    $this->addWhere($customQuery);
    $this->addGroupBy($customQuery);
    $this->filterReturnFields();

    $this->query = new SelectQuery('Contact', $this->params);
    $this->query->merge($customQuery);
  }

  /**
   * Add the conditions to the query.
   *
   * This where it is ensured that only contacts managed by the contactID
   * passed in via the managed_by parameter with the approved relationship types are returned
   * when the managed_by parameter is present.
   *
   * When the unassigned parameter is present and true, only contacts without an
   * active leave approver relationship are returned.
   *
   * Also only contacts with is_deleted = 0 and is_deceased = 0 are returned.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addWhere(CRM_Utils_SQL_Select $query) {
    $hasUnassignedAsTrue = !empty($this->params['unassigned']);
    $hasManagedBy = isset($this->params['managed_by']);
    $activeLeaveManagerCondition = $this->activeLeaveManagerCondition();
    $whereClauses[] = 'a.is_deleted = 0 AND a.is_deceased = 0';

    if($hasUnassignedAsTrue) {
      $whereClauses[] = '(NOT (' . implode(' AND ', $activeLeaveManagerCondition) . ')
                        OR (r.is_active IS NULL AND rt.is_active IS NULL))';

      $whereClauses[] = 'a.id NOT IN(SELECT contact_id_a FROM ' .  Relationship::getTableName() . ' r 
                        INNER JOIN '. RelationshipType::getTableName() . ' rt ON rt.id = r.relationship_type_id 
                        WHERE '. implode(' AND ', $activeLeaveManagerCondition) .')';
    }

    if($hasManagedBy) {
      if (!$this->leaveManagerService->currentUserIsAdmin()) {
        $managerID = (int) CRM_Core_Session::getLoggedInContactID();
        $activeLeaveManagerCondition[] = "r.contact_id_b = {$managerID}";
      }
      $activeLeaveManagerCondition[] = "r.contact_id_b = {$this->params['managed_by']}";

      $whereClauses = array_merge($whereClauses, $activeLeaveManagerCondition);
    }

    $whereClauses = implode(' AND ', $whereClauses);

    $query->where($whereClauses);
  }

  /**
   * Add the joins required to join Contact with Relationship and RelationshipType.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addJoins(CRM_Utils_SQL_Select $query) {
    $joins[] = 'LEFT JOIN ' . Relationship::getTableName() . ' r ON r.contact_id_a = a.id';
    $joins[] = 'LEFT JOIN ' . RelationshipType::getTableName() . ' rt ON rt.id = r.relationship_type_id';
    $query->join(null, $joins);
  }

  /**
   * Add a GROUP BY to the query, group the results.
   * Since the contact table is joined to the relationship table
   * and a contact can have multiple relationships, we may end up
   * with duplicate contacts, hence we group by Contact ID.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addGroupBy(CRM_Utils_SQL_Select $query) {
    $query->groupBy(['a.id']);
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
   * This function allows some fields to be filtered out of the query results.
   */
  private function filterReturnFields() {
    if (empty($this->params['return'])) {
      $allFields = Contact::fieldKeys();
      unset($allFields['created_date'], $allFields['modified_date'], $allFields['hash']);
      $this->params['return'] = $allFields;
    }

    if (!empty($this->params['return'])) {
      $returnFields = array_flip($this->params['return']);
      unset($returnFields['created_date'], $returnFields['modified_date'], $returnFields['hash']);
      $this->params['return'] = array_flip($returnFields);
    }
  }

  /**
   * Returns the conditions needed to add to the Where clause for
   * contacts that have active leave managers
   *
   * @return array
   */
  private function activeLeaveManagerCondition() {
    $today = date('Y-m-d');
    $leaveApproverRelationshipTypes = $this->getLeaveApproverRelationshipsTypesForWhereIn();

    $conditions = [];
    $conditions[] = 'rt.is_active = 1';
    $conditions[] = 'rt.id IN(' . implode(',', $leaveApproverRelationshipTypes) . ')';
    $conditions[] = 'r.is_active = 1';
    $conditions[] = "(r.start_date IS NULL OR r.start_date <= '$today')";
    $conditions[] = "(r.end_date IS NULL OR r.end_date >= '$today')";

    return $conditions;
  }
}

