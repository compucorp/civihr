<?php

trait CRM_HRCore_Upgrader_Steps_1031 {

  /**
   * Disables and Uninstall the Recruitment Extension
   * @return bool
   */
  public function upgrade_1031() {
    $this->up1031_disableAndUninstallRecruitment();

    return TRUE;
  }

  /**
   * disables and then Uninstalls the Recruitment Extensions
   */
  private function up1031_disableAndUninstallRecruitment() {
    civicrm_api3('Extension', 'disable', [
      'keys' => 'org.civicrm.hrrecruitment',
      'api.Extension.uninstall' => ['keys' => 'org.civicrm.hrrecruitment'],
    ]);
  }

}
