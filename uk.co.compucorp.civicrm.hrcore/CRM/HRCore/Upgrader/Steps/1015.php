<?php

trait CRM_HRCore_Upgrader_Steps_1015 {
  
  /**
   * Update is_locked value for phone type option group
   *
   * @return bool
   */
  public function upgrade_1015() {
    // is_active is set here because CiviCRM API defaults the is_active FALSE
    $data = [
      'id' => '$value.id',
      'is_locked' => 1,
      'is_active' => 1
    ];
    civicrm_api3('OptionGroup', 'get', [
      'return' => ['id'],
      'name' => 'phone_type',
      'api.OptionGroup.create' => $data,
    ]);
    
    return TRUE;
  }
}
