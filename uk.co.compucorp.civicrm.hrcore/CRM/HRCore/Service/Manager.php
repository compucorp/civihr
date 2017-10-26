<?php

class CRM_HRCore_Service_Manager {

  /**
   * Returns a list currently active Line Managers for the Contact with the
   * given ID
   *
   * @param int $contactID
   *
   * @return array
   *  An array with the following format:
   *  [ contactID => contactDisplayName ]
   */
  public function getLineManagersFor($contactID) {
    $relationshipTypeID = $this->getRelationshipTypeID('Line Manager is');

    if(!$relationshipTypeID) {
      return [];
    }

    $queryParam = [
      1 => [$contactID, 'Integer'],
      2 => [$relationshipTypeID, 'Integer']
    ];

    $query = '
      SELECT cc.id, cc.display_name
      FROM civicrm_relationship cr
      LEFT JOIN civicrm_contact cc
        ON cr.contact_id_b = cc.id
      WHERE cr.contact_id_a = %1
        AND cr.relationship_type_id = %2
        AND (cr.end_date IS NULL OR cr.end_date >= CURDATE())
        AND (cr.start_date IS NULL OR cr.start_date <= CURDATE())
        AND cr.is_active = 1
        AND cc.is_deleted = 0
    ';
    $result = CRM_Core_DAO::executeQuery($query, $queryParam);

    $lineManagers = [];
    while ($result->fetch()) {
      $lineManagers[$result->id] = $result->display_name;
    }

    return $lineManagers;
  }

  /**
   * Returns the ID of the given Relationship Type
   *
   * @param string $relationshipTypeName
   *
   * @return int|null
   *  NULL will be returned if the Relationship Type cannot be found
   */
  private function getRelationshipTypeID($relationshipTypeName) {
    try {
      $result = civicrm_api3('RelationshipType', 'getsingle', [
        'sequential' => 1,
        'return' => ['id'],
        'name_a_b' => $relationshipTypeName,
      ]);
    } catch (Exception $e) {
      return NULL;
    }

    return $result['id'];
  }
}
