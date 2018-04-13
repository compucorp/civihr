<?php

trait CRM_HRCore_Upgrader_Steps_1008 {

  /**
   * Make the 'Line Manager/Line Manager is' Relationship to be reserved.
   *
   * @return bool
   */
  public function upgrade_1008() {
    $type = civicrm_api3('RelationshipType', 'get', [
      'sequential' => 1,
      'name_a_b' => 'Line Manager is',
      'name_b_a' => 'Line Manager',
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
