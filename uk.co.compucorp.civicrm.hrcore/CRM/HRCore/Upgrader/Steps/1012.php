<?php

trait CRM_HRCore_Upgrader_Steps_1012 {
  
  /**
   * Update is_locked value for phone type option group
   *
   * @return bool
   */
  public function upgrade_1012() {
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
