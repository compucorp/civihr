<?php

trait CRM_HRUI_Upgrader_Steps_4709 {

  /**
   * Upgrade CustomGroup, set Inline_Custom_Data is_reserved Yes
   *
   * @return bool
   */
  public function upgrade_4709() {
    $result = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'return' => ['id'],
      'name' => 'Inline_Custom_Data',
    ]);
  
    civicrm_api3('CustomGroup', 'create', [
      'id' => $result['id'],
      'is_reserved' => 1,
    ]);
    
    return TRUE;
  }

}
