<?php

class CRM_HRCore_Hook_Disable_CustomGroupDisabler {

  /**
   * Handle disabling the custom group and fields for this extension
   */
  public function handle() {
    $customGroups = ['Extended_Demographics'];
    $switcher = Civi::container()->get('civihr.custom_group_status_switcher');

    foreach ($customGroups as $groupName) {
      $switcher->disable($groupName);
    }
  }

}
