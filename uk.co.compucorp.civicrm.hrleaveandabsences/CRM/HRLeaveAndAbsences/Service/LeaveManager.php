<?php

/**
 * This service contains some methods to deal with who is the manager of Leave
 * Requests. Examples, checking if a contact is the "Leave Approver" of another
 * contact of if a contact has permission to administer things under Leave and
 * Absences.
 */
class CRM_HRLeaveAndAbsences_Service_LeaveManager {

  /**
   * Checks contact given by $contactID is managed by the contact given by
   * $managerID.
   *
   * In order to do this, we check if there's a Relationship between both
   * contacts, where contact A ($contactID) "has Leave Approved By" contact B
   * ($managerID), and that if the Relationship is active on the current date.
   *
   * Note about implementation: Due to the impossibility of making complex
   * queries using the CiviCRM API (r.start_date IS NULL OR r.start_date <=
   * CURDATE()), this method uses a SQL query to check the relationship.
   *
   * @param int $contactID
   * @param int $managerID
   *
   * @return bool
   */
  public function isContactManagedBy($contactID, $managerID) {
    $relationshipTable = CRM_Contact_BAO_Relationship::getTableName();
    $relationshipTypeTable = CRM_Contact_BAO_RelationshipType::getTableName();

    $query = "SELECT r.id 
                FROM {$relationshipTable} r
                INNER JOIN {$relationshipTypeTable} rt ON rt.id = r.relationship_type_id
              WHERE r.is_active = 1 AND 
                    rt.is_active = 1 AND 
                    rt.name_a_b = 'has Leave Approved By' AND
                    r.contact_id_a = %1 AND
                    r.contact_id_b = %2 AND
                    (r.start_date IS NULL OR r.start_date <= %3) AND 
                    (r.end_date IS NULL OR r.end_date >= %3)
              ";

    $params = [
      1 => [$contactID, 'Integer'],
      2 => [$managerID, 'Integer'],
      3 => [date('Y-m-d'), 'String']
    ];

    $result = CRM_Core_DAO::executeQuery($query, $params);

    return $result->N > 0;
  }

  /**
   * Checks if the current logged in user is the Leave Manager for the Contact
   * with the given ID.
   *
   * @see CRM_HRLeaveAndAbsences_Service_LeaveManager::isContactManagedBy()
   *
   * @param int $contactID
   *
   * @return bool
   */
  public function currentUserIsLeaveManagerOf($contactID) {
    $currentUserID = CRM_Core_Session::getLoggedInContactID();

    return $this->isContactManagedBy($contactID, $currentUserID);
  }

  /**
   * Checks if the current logged user is a L&A admin. An admin is a user with
   * the "administer leave and absences" permission.
   *
   * @return bool
   */
  public function currentUserIsAdmin() {
    return CRM_Core_Permission::check('administer leave and absences');
  }
}
