<?php

/**
 * A trait with helper methods to be reused among this extension's tests
 */
trait HrUITrait {

  /**
   * Creates a single (Individuals) contact from the provided data.
   *
   * @param array $params should contain first_name and last_name
   * @return int return the contact ID
   * @throws \CiviCRM_API3_Exception
   */
  protected function createContact($params) {
    $result = civicrm_api3('Contact', 'create', array(
      'contact_type' => "Individual",
      'first_name' => $params['first_name'],
      'last_name' => $params['last_name'],
      'display_name' => $params['first_name'] . ' ' . $params['last_name'],
    ));
    return $result['id'];
  }

  /**
   * Creates a relationship between two contacts
   *
   * @param int $contactA contact A ID
   * @param int $contactB contact B ID
   * @param string $relationship_type relationship type label
   * @throws \CiviCRM_API3_Exception
   */
  protected function createRelationship($contactA, $contactB, $relationship_type) {
    $relationshipType = civicrm_api3('RelationshipType', 'getsingle', array(
      'return' => array("id"),
      'name_a_b' => $relationship_type,
    ));
    civicrm_api3('Relationship', 'create', array(
      'contact_id_a' => $contactA,
      'contact_id_b' => $contactB,
      'relationship_type_id' => $relationshipType['id'],
    ));
  }

}
