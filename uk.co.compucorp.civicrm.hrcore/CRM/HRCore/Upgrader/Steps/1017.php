<?php

trait CRM_HRCore_Upgrader_Steps_1017 {

  /**
   * Updates some site configuration
   */
  public function upgrade_1017() {
    $this->up1017_setSettingValue('logging', TRUE);
    $this->up1017_setSettingValue('max_attachments', 10);
    $this->up1017_setSettingValue('maxFileSize', 10);

    return TRUE;
  }

  /**
   * Sets a CiviCRM setting value
   *
   * @param string $name
   * @param mixed $value
   */
  private function up1017_setSettingValue($name, $value) {
    civicrm_api3('Setting', 'create', [$name => $value]);
  }

}
