<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1000 {

  /**
   * Updates the labels of the "1/2 AM" and "1/2 PM" option values to "Half-day AM"
   * and "Half-day PM" respectively
   *
   * @return bool
   */
  public function upgrade_1000() {
    $this->up1000_updateHalfDayAmLabel();
    $this->up1000_updateHalfDayPmLabel();

    return true;
  }

  /**
   * Updates the label of the "1/2 AM" option value
   */
  private function up1000_updateHalfDayAmLabel() {
    $this->up1000_updateOptionValueLabel('half_day_am', 'Half-day AM');
  }

  /**
   * Updates the label of the "1/2 PM" option value
   */
  private function up1000_updateHalfDayPmLabel() {
    $this->up1000_updateOptionValueLabel('half_day_pm', 'Half-day PM');
  }

  /**
   * Updates the option value with the given $name with the given $label
   *
   * @param string $name
   * @param string $label
   */
  private function up1000_updateOptionValueLabel($name, $label) {
    civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => 'hrleaveandabsences_leave_request_day_type',
      'name' => ['IN' => [$name]],
      'api.OptionValue.create' => ['id' => '$value.id', 'label' => $label],
    ]);
  }
}
