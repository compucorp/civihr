<?php

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1003 {

  /**
   * Change fields used to store amounts of days from integer to decimal
   *
   * @return bool
   */
  public function upgrade_1003() {
    $queries = [
      "ALTER TABLE civicrm_hrleaveandabsences_absence_type MODIFY max_consecutive_leave_days DECIMAL(20,2)",
      "ALTER TABLE civicrm_hrleaveandabsences_absence_type MODIFY default_entitlement DECIMAL(20,2) NOT NULL COMMENT 'The number of days entitled for this type'",
      "ALTER TABLE civicrm_hrleaveandabsences_absence_type MODIFY max_leave_accrual DECIMAL(20,2) COMMENT 'Value is the number of days that can be accrued. Null means unlimited'",
      "ALTER TABLE civicrm_hrleaveandabsences_absence_type MODIFY max_number_of_days_to_carry_forward DECIMAL(20,2)"
    ];

    foreach($queries as $query) {
      CRM_Core_DAO::executeQuery($query);
    }

    return true;
  }
}
