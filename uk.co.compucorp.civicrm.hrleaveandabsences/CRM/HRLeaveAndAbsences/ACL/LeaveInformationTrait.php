<?php

trait CRM_HRLeaveAndAbsences_ACL_LeaveInformationTrait {

  use CRM_HRLeaveAndAbsences_Service_SettingsManagerTrait;

  /**
   * For any leave information (Entitlement, Leave Requests etc) the access
   * rules are:
   *   - Staff members can only see their own information
   *   - Managers can see their own information + the information from their
   *     managees
   *
   * This method creates ACL clauses that can be added to queries in order to
   * filter results to follow these rules.
   *
   * The manager > managee relationship is determined by checking if there's an
   * active relationship between the two contacts and that the type of this
   * relationship is one of those configured as a "Leave Approver Relationship
   * Type" on the extension's General Settings.
   *
   * @return string
   *   Query String
   */
  public function getLeaveInformationACLClauses() {
    $contactsTable = CRM_Contact_BAO_Contact::getTableName();
    $relationshipTable = CRM_Contact_BAO_Relationship::getTableName();
    $relationshipTypeTable = CRM_Contact_BAO_RelationshipType::getTableName();

    $conditions = $this->getLeaveInformationACLWhereConditions('c.id');

    $query = "IN (
      SELECT c.id
      FROM {$contactsTable} c
      LEFT JOIN {$relationshipTable} r ON c.id = r.contact_id_a
      LEFT JOIN {$relationshipTypeTable} rt ON rt.id = r.relationship_type_id
      WHERE $conditions
    )";

    return $query;
  }

  /**
   * Builds the conditions that will be used in the WHERE part of the
   * ACL clause created by getLeaveInformationACLClauses.
   *
   * These conditions are supposed to work in different SELECT queries, so it's
   * possible to pass the name/alias of the field of the contact ID. For
   * example, if the query is based on Leave Requests (that is, we want leave
   * requests linked to a given contact), then we can pass something like
   * leave_request.contact_id to $contactIDField.
   *
   * @param string $contactIDField
   *
   * @return string
   */
  public function getLeaveInformationACLWhereConditions($contactIDField) {
    $loggedInUserID = (int) CRM_Core_Session::getLoggedInContactID();

    $conditions = [];
    $conditions[] = "({$contactIDField} = {$loggedInUserID})";

    $whereClause = $this->getLeaveApproverRelationshipWhereClause();
    if($whereClause) {
      $conditions[] = $whereClause;
    }

    $conditions = implode(' OR ', $conditions);

    return $conditions;
  }

  /**
   * Build the where clause to filter entities based on an existing active
   * relationship of the current logged in user with another contact, where the
   * logged in user is a leave approver.
   *
   * It assumes this will be added to a query where there are joins with the
   * civicrm_relationship and civicrm_relationship_type tables, where the alias
   * are r and rt, respectively.
   *
   * @return string
   */
  private function getLeaveApproverRelationshipWhereClause() {
    $loggedInUserID = (int) CRM_Core_Session::getLoggedInContactID();
    $today = date('Y-m-d');

    $leaveApproverRelationships = $this->getLeaveApproverRelationshipsTypes();

    $clause = '';
    if (!empty($leaveApproverRelationships)) {
      $clause = "(
        r.is_active = 1 AND
        rt.is_active = 1 AND
        rt.id IN(" . implode(',', $leaveApproverRelationships) . ") AND
        r.contact_id_b = {$loggedInUserID} AND 
        (r.start_date IS NULL OR r.start_date <= '$today') AND
        (r.end_date IS NULL OR r.end_date >= '$today')
      )";
    }

    return $clause;
  }
}
