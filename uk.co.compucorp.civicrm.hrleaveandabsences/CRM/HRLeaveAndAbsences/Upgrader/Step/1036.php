<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1036 {

  /**
   * Updates absence type category for existing records
   *
   * @return bool
   */
  public function upgrade_1036() {
    $categoryOptions = array_flip(AbsenceType::buildOptions('category', 'validate'));
    $absenceTypes = $this->up1036_getAbsenceTypes();
    foreach ($absenceTypes as $absenceType) {
      if (empty($absenceType->category)) {
        $this->up1036_updateAbsenceTypeCategory($absenceType, $categoryOptions);
      }
    }

    return TRUE;
  }

  /**
   * Retrieves all absence types
   *
   * @return array
   */
  private function up1036_getAbsenceTypes() {
    $absenceTypes = [];
    $absenceType = new AbsenceType();
    $absenceType->find();
    while($absenceType->fetch()) {
      $absenceTypes[] = clone $absenceType;
    }

    return $absenceTypes;
  }

  /**
   * Updates absence type category and optional fields
   *
   * @param CRM_HRLeaveAndAbsences_BAO_AbsenceType $absenceType
   * @param array $categoryOptions
   */
  private function up1036_updateAbsenceTypeCategory($absenceType, $categoryOptions) {
    if ($absenceType->is_sick) {
      $absenceType->category = $categoryOptions['sickness'];
    }

    if ($absenceType->allow_accruals_request) {
      $absenceType->category = $categoryOptions['toil'];
    }

    if ($absenceType->is_sick && $absenceType->allow_accruals_request) {
      $absenceType->category = $categoryOptions['custom'];
    }

    if (empty($absenceType->category)) {
      $absenceType->category = $categoryOptions['leave'];
    }
    elseif ($absenceType->category === $categoryOptions['sickness']) {
      $absenceType->add_public_holiday_to_entitlement = 0;
      $absenceType->allow_accruals_request = 0;
      $absenceType->must_take_public_holiday_as_leave = 0;
      $absenceType->allow_carry_forward = 0;
    }

    AbsenceType::create($absenceType->toArray());
  }
}
