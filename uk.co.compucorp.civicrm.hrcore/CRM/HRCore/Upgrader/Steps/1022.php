<?php

trait CRM_HRCore_Upgrader_Steps_1022 {

  /**
   * Removes Recruitment menu item
   *
   * @return bool
   */
  public function upgrade_1022() {
    $this->up1022_removeRecruitmentMenuItem('Recruitment');
    $this->up1022_disableAndUninstallRecruitment();

    return TRUE;
  }

  /**
   * Removes Recruitment menu item, by deleting its entry on the DB
   */
  private function up1022_removeRecruitmentMenuItem($label) {
    civicrm_api3('Navigation', 'get', [
      'label' => $label,
      'api.Navigation.delete' => ['id' => '$value.id'],
    ]);
  }

  /**
   * disables and then Uninstalls the Recruitment Extensions
   */
  private function up1022_disableAndUninstallRecruitment() {
    civicrm_api3('Extension', 'disable', [
      'keys' => 'org.civicrm.hrrecruitment',
      'api.Extension.uninstall' => ['keys' => 'org.civicrm.hrrecruitment'],
    ]);
  }

}
