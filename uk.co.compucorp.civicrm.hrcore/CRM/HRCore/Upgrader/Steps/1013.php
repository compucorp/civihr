<?php

trait CRM_HRCore_Upgrader_Steps_1013 {

  /**
   * Updates permissions on the items in the "Administer" submenu
   *
   * @return bool
   */
  public function upgrade_1013() {
    $domain = CRM_Core_Config::domainID();
    $params = ['return' => 'id', 'name' => 'Administer', 'domain_id' => $domain];
    $administerId = (int) civicrm_api3('Navigation', 'getvalue', $params);

    $this->up1013_replaceExistingAdministerItemsPermission($administerId);

    return TRUE;
  }

  /**
   * Replaces all existing 'administer CiviCRM' permissions in the
   * 'Administer' submenu with 'access root menu items and configurations'
   *
   * @param int $administerId
   */
  private function up1013_replaceExistingAdministerItemsPermission($administerId) {
    $allChildren = [];
    $this->up1013_findAllChildElements($administerId, $allChildren);
    $replacement = 'access root menu items and configurations';
    $original = 'administer CiviCRM';

    $exceptions = ['Custom Fields'];

    foreach ($allChildren as $child) {
      if (in_array($child['name'], $exceptions)) {
        continue;
      }

      $permissions = CRM_Utils_Array::value('permission', $child);
      $permissions = str_replace($original, $replacement, $permissions);
      $params = ['id' => $child['id'], 'permission' => $permissions];
      civicrm_api3('Navigation', 'create', $params);
    }
  }

  /**
   * Recursively search for all children of a given parent ID
   *
   * @param int $parentId
   * @param array $allChildren
   */
  private function up1013_findAllChildElements($parentId, &$allChildren) {
    $params = ['parent_id' => $parentId];
    $children = civicrm_api3('Navigation', 'get', $params)['values'];
    $allChildren = array_merge($allChildren, $children);

    foreach ($children as $child) {
      $this->up1013_findAllChildElements($child['id'], $allChildren);
    }
  }

}
