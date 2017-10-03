<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1005 {

  /**
   * Creates the Absence Type calculation unit option group
   * and option values.
   *
   * @return bool
   */
  public function upgrade_1005() {
    $this->up1005_createCalculationUnitOptionGroupAndValues();

    return true;
  }

  /**
   * Creates the hrleaveandabsences_absence_type_calculation_unit
   * Option group and the Days and Hours option values by importing
   * the option group Xml file and processing it.
   */
  private function up1005_createCalculationUnitOptionGroupAndValues() {
    $result = civicrm_api3('OptionGroup', 'getcount', [
      'name' => 'hrleaveandabsences_absence_type_calculation_unit',
    ]);

    if($result == 0) {
      $file = $this->extensionDir . '/xml/option_groups/absence_type_calculation_unit_install.xml';
      $this->executeCustomDataFileByAbsPath($file);
    }
  }
}
