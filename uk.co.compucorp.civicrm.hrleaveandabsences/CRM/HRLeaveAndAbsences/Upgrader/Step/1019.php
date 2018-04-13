<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1019 {

  /**
   * Make the 'is Leave Approver of/has Leave Approved by'
   * Relationship to be reserved.
   *
   * @return bool
   */
  public function upgrade_1019() {
    $type = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'name_a_b' => 'has Leave Approved by',
      'name_b_a' => 'is Leave Approver of',
    ]);

    if ($type['count'] != 1) {
      return TRUE;
    }

    $type = array_shift($type['values']);
    $type['is_reserved'] = 1;

    civicrm_api3('RelationshipType', 'create', $type);

    return TRUE;
  }
}
