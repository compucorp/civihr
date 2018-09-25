<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1035 {

  /**
   * Updates absence type category for existing records
   *
   * @return bool
   */
  public function upgrade_1035() {
    $absenceTypes = civicrm_api3('AbsenceType', 'get');

    foreach ($absenceTypes['values'] as $absenceType) {
      $this->up1035_updateAbsenceTypeCategory($absenceType);
    }

    return TRUE;
  }

  /**
   * Updates absence type category and optional fields
   *
   * @param $absenceType
   */
  private function up1035_updateAbsenceTypeCategory($absenceType) {
    $category = $absenceType['is_sick'] ? AbsenceType::CATEGORY_SICKNESS : AbsenceType::CATEGORY_CUSTOM;
    if ($absenceType['is_sick'] && $absenceType['allow_accrue_in_the_past']) {
      $category = AbsenceType::CATEGORY_CUSTOM;
    }

    $updateOptions = [
      'id' => $absenceType['id'],
      'category' => $category
    ];
    if ($category === AbsenceType::CATEGORY_SICKNESS) {
      $updateOptions['add_public_holiday_to_entitlement'] = 0;
      $updateOptions['allow_accruals_request'] = 0;
      $updateOptions['must_take_public_holiday_as_leave'] = 0;
      $updateOptions['allow_carry_forward'] = 0;
    }

    civicrm_api3('AbsenceType', 'create', $updateOptions);
  }
}
