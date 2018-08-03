<?php

trait CRM_HRCore_Upgrader_Steps_1024 {

  /**
   * Disables and Uninstall the Recruitment Extension
   * @return bool
   */
  public function upgrade_1024() {
    $this->up1024_disableAndUninstallRecruitment();

    return TRUE;
  }

  /**
   * disables and then Uninstalls the Recruitment Extensions
   */
  private function up1024_disableAndUninstallRecruitment() {
    civicrm_api3('Extension', 'disable', [
      'keys' => 'org.civicrm.hrrecruitment',
      'api.Extension.uninstall' => ['keys' => 'org.civicrm.hrrecruitment'],
    ]);
  }

}
