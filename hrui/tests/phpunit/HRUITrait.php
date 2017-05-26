<?php

/**
 * A trait with helper methods to be reused among this extension's tests
 */
trait HrUITrait {
  /**
   * Creates a relationship between two contacts
   *
   * @param int $contactA contact A ID
   * @param int $contactB contact B ID
   * @param string $relationship_type relationship type label
   * @throws \CiviCRM_API3_Exception
   */
  protected function createRelationship($contactA, $contactB, $relationship_type) {
    $relationshipType = civicrm_api3('RelationshipType', 'getsingle', [
      'return' => ['id'],
      'name_a_b' => $relationship_type,
    ]);

    civicrm_api3('Relationship', 'create', [
      'contact_id_a' => $contactA,
      'contact_id_b' => $contactB,
      'relationship_type_id' => $relationshipType['id'],
    ]);
  }

}
