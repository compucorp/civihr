<?php

trait CRM_HRUI_Upgrader_Steps_4706 {

  /**
   * Upgrader set icons for civicrm core menus
   *
   * @return bool
   */
  public function upgrade_4706() {
    $menuToIcons = [
      'Search...' => 'fa fa-search',
      'Contacts'=> 'fa fa-users',
      'Administer' => 'fa fa-cog',
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
