<?php

trait CRM_HRCore_Upgrader_Steps_1007 {

  /**
   * Make the 'Line Manager/Line Manager is' Relationship to be reserved.
   *
   * @return bool
   */
  public function upgrade_1007() {
    civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'name_a_b' => 'Line Manager is',
      'name_b_a' => 'Line Manager',
      'api.RelationshipType.create' => ['id' => '$value.id', 'is_reserved' => 1],
    ]);

    return TRUE;
  }
}
