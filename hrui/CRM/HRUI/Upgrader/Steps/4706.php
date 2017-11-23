<?php

trait CRM_HRUI_Upgrader_Steps_4706 {

  /**
   * Sets icons for civicrm core menus
   *
   * @return bool
   */
  public function upgrade_4706() {
    $menuToIcons = [
      'Search...' => 'crm-i fa-search',
      'Contacts'=> 'crm-i fa-users',
      'Administer' => 'crm-i fa-cog',
    ];

    foreach ($menuToIcons as $menuName => $menuIcon) {
      $params = [
        'name' => $menuName,
        'api.Navigation.create' => ['id' => '$value.id', 'icon' => $menuIcon],
        'parent_id' => ['IS NULL' => true],
      ];

      civicrm_api3('Navigation', 'get', $params);
    }

    return TRUE;
  }
}
