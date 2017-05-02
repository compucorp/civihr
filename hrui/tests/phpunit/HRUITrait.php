<?php

/**
 * A trait with helper methods to be reused among this extension's tests
 */
trait HrUITrait {

  /**
   * Creates a single (Individuals) contact from the provided data.
   *
   * @param $firstName
   * @param $lastName
   *
   * @return mixed
   */
  protected function createContact($firstName, $lastName) {
    $result = civicrm_api3('Contact', 'create', array(
      'contact_type' => "Individual",
      'first_name' => $firstName,
      'last_name' => $lastName,
      'display_name' => $firstName . ' ' . $lastName,
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

  /**
   * @param $labelAtoB
   * @param $labelBtoA
   *
   * @return array
   */
  protected function createRelationshipType($labelAtoB, $labelBtoA) {
    return civicrm_api3('RelationshipType', 'create', [
      'name_a_b' => $labelAtoB,
      'name_b_a' => $labelBtoA,
      'contact_type_a' => 'Individual',
      'contact_type_b' => 'Individual',
    ]);
  }

}
