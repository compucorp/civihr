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
    $this->filterReturnFields();

    $this->query = new SelectQuery('Contact', $this->params);
    $this->query->merge($customQuery);
  }

  /**
   * Add the conditions to the query.
   *
   * This where it is ensured that only contacts managed by the contactID
   * passed in via the managed_by parameter with the approved relationship types are returned.
   * Also only contacts with is_deleted = 0 and is_deceased = 0 are returned.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addWhere(CRM_Utils_SQL_Select $query) {
    $today = date('Y-m-d');
    $managerID = (int) CRM_Core_Session::getLoggedInContactID();

    if ($this->leaveManagerService->currentUserIsAdmin()) {
      $managerID = $this->params['managed_by'];
    }

    $leaveApproverRelationships = $this->getLeaveApproverRelationshipsTypes();

    $whereClauses[] = "(
      r.is_active = 1 AND
      rt.is_active = 1 AND
      rt.id IN(" . implode(',', $leaveApproverRelationships) . ") AND
      r.contact_id_b = {$managerID} AND 
      (r.start_date IS NULL OR r.start_date <= '$today') AND
      (r.end_date IS NULL OR r.end_date >= '$today')
    )";
    $whereClauses[] = 'a.is_deleted = 0 AND a.is_deceased = 0';
    $whereClauses = implode(' AND ', $whereClauses);

    $query->where($whereClauses);
  }

  /**
   * Add the joins required to join Contact with Relationship and RelationshipType.
   *
   * @param \CRM_Utils_SQL_Select $query
   */
  private function addJoins(CRM_Utils_SQL_Select $query) {
    $joins[] = 'INNER JOIN ' . Relationship::getTableName() . ' r ON r.contact_id_a = a.id';
    $joins[] = 'INNER JOIN ' . RelationshipType::getTableName() . ' rt ON rt.id = r.relationship_type_id';
    $query->join(null, $joins);
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
}

