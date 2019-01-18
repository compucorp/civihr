<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1035 {

  /**
   * Removes the is_default column from absence type table.
   * The initial use for setting default type is changed due to introduction of
   * categories. This means multiple absence type can belong to same category,
   * making is_default irrelevant.
   *
   * @return bool
   */
  public function upgrade_1035() {
    $absenceTypeTable = AbsenceType::getTableName();
    if (SchemaHandler::checkIfFieldExists($absenceTypeTable, 'is_default')) {
      CRM_Core_DAO::executeQuery("ALTER TABLE {$absenceTypeTable} DROP COLUMN is_default");
      // This helps to fix issues with trigger when logging is enabled
      // as a result of log table structure not being updated with column modifications
      $logging = new CRM_Logging_Schema();
      $logging->fixSchemaDifferencesFor($absenceTypeTable, [], TRUE);
    }

    return TRUE;
  }
}
