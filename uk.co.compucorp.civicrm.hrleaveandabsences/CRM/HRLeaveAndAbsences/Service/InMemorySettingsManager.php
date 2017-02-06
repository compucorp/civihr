<?php

use CRM_HRLeaveAndAbsences_Service_SettingsManager as SettingsManager;

class CRM_HRLeaveAndAbsences_Service_InMemorySettingsManager implements SettingsManager {

  private $settings = [];

  public function get($setting) {
    return isset($this->settings[$setting]) ? $this->settings[$setting] : null;
  }

  public function set($setting, $value) {
    $this->settings[$setting] = $value;
  }
}
