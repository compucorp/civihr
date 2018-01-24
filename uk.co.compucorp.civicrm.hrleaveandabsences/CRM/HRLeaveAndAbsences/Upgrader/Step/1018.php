<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1018 {

  /**
   * Make the is Leave Approver of/has Leave Approved by Relationship
   * to be reserved.
   *
   * @return bool
   */
  public function upgrade_1018() {
    civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'name_a_b' => 'has Leave Approved by',
      'name_b_a' => 'is Leave Approver of',
      'api.RelationshipType.create' => ['id' => '$value.id', 'is_reserved' => 1],
    ]);
    
    return TRUE;
  }
}
