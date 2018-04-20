<?php

trait CRM_HRUI_Upgrader_Steps_4710 {
  /**
   * Upgrade CustomGroup, set Inline_Custom_Data title to NI/SSN Number
   *
   * @return bool
   */
  public function upgrade_4710() {
    $result = civicrm_api3('CustomGroup', 'get', [
      'return' => ['id'],
      'name' => 'Inline_Custom_Data',
    ]);
    
    civicrm_api3('CustomGroup', 'create', [
      'id' => $result['id'],
      'title' => 'NI/SSN Number',
    ]);
    
    return TRUE;
  }
  
}
