<?php

trait CRM_HRCore_Upgrader_Steps_1017 {

  /**
   * Adds a shortcut to the activity types edit link in the configuration menu
   *
   * @return bool
   */
  public function upgrade_1017() {
    $domain = CRM_Core_Config::domainID();
    $params = ['return' => 'id', 'name' => 'Administer', 'domain_id' => $domain];
    $administerId = (int) civicrm_api3('Navigation', 'getvalue', $params);

    $this->up1017_addActivityTypesShortcut($administerId);

    return TRUE;
  }

  /**
   * Adds a shortcut to the edit activity types
   *
   * @param int $administerId
   */
  private function up1017_addActivityTypesShortcut($administerId) {
    $result = $this->up1017_createNavItem(
      'Activity Types',
      'access CiviCRM',
      $administerId,
      ['url' => 'civicrm/admin/options/activity_type?reset=1']
    );

    // weight cannot be set when you're creating first time
    $id = $result['id'];
    civicrm_api3('Navigation', 'create', ['id' => $id, 'weight' => -94]);
  }

  /**
   * Creates a navigation menu item using the API
   *
   * @param string $name
   * @param string $permission
   * @param int $parentID
   * @param array $params
   *
   * @return array
   */
  private function up1017_createNavItem(
    $name,
    $permission,
    $parentID,
    $params = []
  ) {
    $params = array_merge([
      'name' => $name,
      'label' => ts($name),
      'permission' => $permission,
      'parent_id' => $parentID,
      'is_active' => 1,
    ], $params);

    $existing = civicrm_api3('Navigation', 'get', $params);

    if ($existing['count'] > 0) {
      return array_shift($existing['values']);
    }

    return civicrm_api3('Navigation', 'create', $params);
  }

}
