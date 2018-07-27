<?php

use CRM_HRCore_Service_CustomGroupStatusSwitcher as CustomGroupStatusSwitcher;

class CRM_HRCore_Hook_Enable_CustomGroupEnabler {

  /**
   * Handle enabling the custom group and fields for this extension
   */
  public function handle() {
    $customGroups = ['Extended_Demographics'];

    // cannot use container service for this extension before being enabled
    $switcher = new CustomGroupStatusSwitcher();

    foreach ($customGroups as $groupName) {
      $switcher->enable($groupName);
    }
  }

}
