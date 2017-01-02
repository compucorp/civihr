<?php

trait CRM_HRCore_Upgrader_Steps_1003 {

  public function upgrade_1003() {
    $this->updateDisplayPreferencesSettings();

    return TRUE;
  }

  /**
   * Updates Display Preferences settings by hiding
   * Tags and Groups from contact related screens.
   */
  private function updateDisplayPreferencesSettings() {
    $viewOptionsSettings = civicrm_api3('Setting', 'get', array(
      'sequential' => 1,
      'return' => array("contact_view_options"),
    ));

    $viewOptions = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'return' => array("id"),
      'option_group_id' => "contact_view_options",
      'name' => array('IN' => array("tag", "group")),
    ));

    $toDeleteOptions = [];
    if (!empty($viewOptions['values'])) {
      $toDeleteOptions = array_column($viewOptions['values'], 'id');
    }

    $newOptions = [];
    if(!empty($viewOptionsSettings['values']['contact_view_options'])) {
      $currentOptions = $viewOptionsSettings['values']['contact_view_options'];

      foreach ($currentOptions as $option) {
        if (!in_array($option, $toDeleteOptions)) {
          $newOptions[] = $option;
        }
      }
    }

    civicrm_api3('Setting', 'create', array(
      'sequential' => 1,
      'contact_view_options' => $newOptions,
    ));
  }

}