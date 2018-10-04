<?php

trait CRM_HRCore_Upgrader_Steps_1036 {

  /**
   * Enables the CiviTask and CiviDocument extension for sites
   * that previously have these components disabled due to a bug.
   */
  public function upgrade_1036() {
    $getResult = civicrm_api3('setting', 'getsingle', [
      'return' => ['enable_components'],
    ]);

    $enabledComponents = $getResult['enable_components'];
    $componentsToEnable = ['CiviTask', 'CiviDocument'];
    //check if these components are already enabled.
    if (count(array_intersect($enabledComponents, $componentsToEnable)) == 2) {
      return TRUE;
    }

    $enabledComponents = array_merge($enabledComponents, $componentsToEnable);

    civicrm_api3('setting', 'create', [
      'enable_components' => array_unique($enabledComponents),
    ]);

    return TRUE;
  }
}
