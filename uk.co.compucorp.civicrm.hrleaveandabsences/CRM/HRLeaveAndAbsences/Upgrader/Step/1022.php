<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1022 {
  
  /**
   * Create new option group hrleaveandabsences_work_pattern_change_reason
   * with values As per contract, Change in contractual hours and
   * Change in contract type.
   *
   * @return bool
   */
  public function upgrade_1022() {
    $import = new CRM_Utils_Migrate_Import();
    $extension = 'uk.co.compucorp.civicrm.hrleaveandabsences';
    $file = 'xml/option_groups/work_pattern_change_reason_install.xml';
  
    $import->run(CRM_Core_Resources::singleton()->getPath($extension, $file));
    return TRUE;
  }
}
