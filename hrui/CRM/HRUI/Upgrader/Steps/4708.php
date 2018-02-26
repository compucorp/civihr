<?php

trait CRM_HRUI_Upgrader_Steps_4708 {

  /**
   * Enable the Activities tab on the contact summary page. The contact
   * tabs are stored in the contact_view_options option group and the
   * enabled tabs are stored in the civicrm settings table.
   *
   * @return bool
   */
  public function upgrade_4708() {
    $result = civicrm_api3('Setting', 'get', [
      'sequential'=> 1,
      'return' => 'contact_view_options'
    ]);
    $enabledTabs = $result['values'][0]['contact_view_options'];

    $options = CRM_Core_OptionGroup::values('contact_view_options', TRUE);
    $activityTabValue = $options['Activities'];

    if(in_array($activityTabValue, $enabledTabs)) {
      return TRUE;
    }

    $enabledTabs[] = $activityTabValue;
    civicrm_api3('Setting', 'create', ['contact_view_options' => $enabledTabs]);

    return TRUE;
  }
}
