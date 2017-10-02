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
   * Option group and the Days and Hours option values.
   */
  private function up1005_createCalculationUnitOptionGroupAndValues() {
    $result = civicrm_api3('OptionGroup', 'getcount', [
      'name' => 'hrleaveandabsences_absence_type_calculation_unit',
    ]);

    if($result == 0) {
      civicrm_api3('OptionGroup', 'create', [
        'name' => 'hrleaveandabsences_absence_type_calculation_unit',
        'title' => 'Absence Type Calculation Units',
        'is_reserved' => 1,
        'is_active' => 1,
      ]);

      $options[] = ['name' => 'days', 'label' => 'Days', 'value' => 1, 'weight' => 1];
      $options[] = ['name' => 'hours', 'label' => 'Hours', 'value' => 2, 'weight' => 2];
      foreach ($options as $option) {
        $this->up1005_createCalculationUnitOptionValue($option);
      }
    }
  }

  /**
   * Creates an option value for the hrleaveandabsences_absence_type_calculation_unit
   * Option group.
   *
   * @param array $params
   */
  private function up1005_createCalculationUnitOptionValue($params) {
    civicrm_api3('OptionValue', 'create', [
      'option_group_id' => 'hrleaveandabsences_absence_type_calculation_unit',
      'name' => $params['name'],
      'label' => $params['label'],
      'value' => $params['value'],
      'weight' => $params['weight'],
      'is_reserved' => 1,
      'is_default' => 0,
      'is_active' => 1
    ]);
  }
}
