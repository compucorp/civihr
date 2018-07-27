<?php

class CRM_HRCore_Hook_Uninstall_CustomGroupRemover {

  /**
   * Handle removing the custom groups added by this extension
   */
  public function handle() {
    $customGroups = [
      'Extended_Demographics'
    ];

    foreach ($customGroups as $customGroupName) {
      $params = ['return' => 'id', 'name' => $customGroupName];
      $result = civicrm_api3('CustomGroup', 'get', $params);

      if ($result['count'] != 1) {
        continue;
      }
      $customGroup = array_shift($result['values']);

      civicrm_api3('CustomGroup', 'delete', ['id' => $customGroup['id']]);
    }
  }
}
