<?php

trait CRM_HRCore_Upgrader_Steps_1032 {

  /**
   * Deletes some of vacancy submenu
   *
   * @return bool
   */
  public function upgrade_1032() {
    $this->up1032_removeRecruitmentSubmenus([
      'Search by Application Form Fields',
      'Search by Evaluation Criteria',
    ]);

    return TRUE;
  }

  /**
   * Deletes some of vacancy submenu left after uninstalling the extension
   *
   * @param array $menusToRemove
   */
  private function up1032_removeRecruitmentSubmenus($menusToRemove) {
    foreach ($menusToRemove as $submenuLabel) {
      civicrm_api3('Navigation', 'get', [
        'label' => $submenuLabel,
        'api.Navigation.delete' => ['id' => '$value.id'],
      ]);
    }
  }

}
