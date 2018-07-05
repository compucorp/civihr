<?php

trait CRM_HRCore_Upgrader_Steps_1020 {

  /**
   * Removes duplicated "Localise CiviHR" menu item
   *
   * This upgrader will:
   * - Delete any menu item with the "Localise CiviHR" label
   * - Makes sure that one menu item with the "Localise CiviCRM" label exists
   *
   * On the interface, due to the word replace in the HRUI extension, the
   * "Localise CiviCRM" label will be displayed as "Localise CiviHR".
   */
  public function upgrade_1020() {
    $this->up1020_deleteLocaliseCiviHRMenuItem();
    $this->up1020_createLocaliseCiviCRMMenuItemIfItDoesNotExist();

    return TRUE;
  }

  /**
   * Deletes a menu item with the "Localise CiviHR" label, if it exists.
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function up1020_deleteLocaliseCiviHRMenuItem() {
    civicrm_api3('Navigation', 'get', [
      'sequential' => 1,
      'label' => 'Localise CiviHR',
      'api.Navigation.delete' => ['id' => '$value.id'],
    ]);
  }

  /**
   * Creates a new menu item with the "Localise CiviCRM" label, if not such
   * item exists.
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function up1020_createLocaliseCiviCRMMenuItemIfItDoesNotExist() {
    $administerId = $this->up1020_getAdministerMenuId();

    if (!$administerId) {
      return;
    }

    $params = [
      'name' => 'Localise CiviCRM',
      'label' => 'Localise CiviCRM',
      'permission' => 'access CiviCRM',
      'parent_id' => $administerId,
      'url' => 'civicrm/admin/setting/localization?reset=1'
    ];

    $result = civicrm_api3('Navigation', 'get', $params);

    if ($result['count'] == 0) {
      $params['is_active'] = 1;
      $newItem = civicrm_api3('Navigation', 'create', $params);

      $firstAdministerChild = $this->up1020_getFirstChildOfParent($administerId);
      // makes Localise CiviCRM the first
      // CiviCRM does not allow to set the weight when creating a new item, so
      // we need to update that after it has been created
      civicrm_api3('Navigation', 'create', [
        'id' => $newItem['id'],
        'weight' => ((int) $firstAdministerChild['weight']) - 1
      ]);
    }
  }

  /**
   * Returns the first child menu item for the item with the given
   * parent ID.
   *
   * The get the first child, the weight of the children will be used
   * for ordering.
   *
   * @param int $parentID
   *
   * @return array
   *   An array representing the menu item record
   *
   * @throws \CiviCRM_API3_Exception
   */
  private function up1020_getFirstChildOfParent($parentID) {
    $result = civicrm_api3('Navigation', 'get', [
      'sequential' => 1,
      'parent_id' => $parentID,
      'options' => ['sort' => 'weight', 'limit' => 1],
    ])['values'];

    return array_shift($result);
  }

  /**
   * Returns the ID of the "Administer" menu for the current domain
   *
   * @return int
   */
  private function up1020_getAdministerMenuId() {
    $domain = CRM_Core_Config::domainID();

    try {
      $administerId = (int) civicrm_api3('Navigation', 'getvalue', [
        'return' => 'id',
        'name' => 'Administer',
        'domain_id' => $domain
      ]);
    } catch (Exception $e) {
      return NULL;
    }

    return $administerId;
  }

}
