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
    $loggedInUserID = (int) CRM_Core_Session::getLoggedInContactID();
    $today = date('Y-m-d');

    $conditions = [];
    $conditions[] = "(c.id = {$loggedInUserID})";

    $leaveApproverRelationships = $this->getLeaveApproverRelationshipsTypes();

    if(!empty($leaveApproverRelationships)) {
      $conditions[] = "(
        r.is_active = 1 AND
        rt.is_active = 1 AND
        rt.id IN(" . implode(',', $leaveApproverRelationships) . ") AND
        r.contact_id_b = {$loggedInUserID} AND 
        (r.start_date IS NULL OR r.start_date <= '$today') AND
        (r.end_date IS NULL OR r.end_date >= '$today')
      )";
    }

    $conditions = implode(' OR ', $conditions);

    $query = "IN (
      SELECT c.id
      FROM {$contactsTable} c
      LEFT JOIN {$relationshipTable} r ON c.id = r.contact_id_a
      LEFT JOIN {$relationshipTypeTable} rt ON rt.id = r.relationship_type_id
      WHERE $conditions
    )";

    return $query;
  }
}
