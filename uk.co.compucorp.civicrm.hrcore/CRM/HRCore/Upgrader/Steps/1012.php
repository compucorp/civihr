<?php

trait CRM_HRCore_Upgrader_Steps_1012 {

  /**
   * Removes relationship types that are not necessary for CiviHR.
   */
  public function upgrade_1012() {
    $relationshipsToBeDeleted = [
      'Case Coordinator is',
      'Employee of',
      'Head of Household for',
      'Household member of'
    ];

    $result = civicrm_api3('RelationshipType', 'get', [
      'name_a_b' => [ 'IN' => $relationshipsToBeDeleted ]
    ]);

    foreach($result['values'] as $relationship) {
      civicrm_api3('RelationshipType', 'delete', [
        'id' => $relationship['id']
      ]);
    }

    return TRUE;
  }
}
