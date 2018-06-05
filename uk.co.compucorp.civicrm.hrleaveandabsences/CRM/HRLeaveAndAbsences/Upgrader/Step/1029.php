<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1029 {
  /**
   * Adds the hide_label field to the Absence Type table
   *
   * @return bool
   */
  public function upgrade_1029() {
    $absenceTypeTable = AbsenceType::getTableName();

    if(!SchemaHandler::checkIfFieldExists($absenceTypeTable, 'hide_label')) {
      CRM_Core_DAO::executeQuery("
        ALTER TABLE {$absenceTypeTable} 
        ADD hide_label tinyint DEFAULT 0 
        COMMENT 'This controls the visibility of the Leave Type label in the calendar and feeds.'");
    }

    return true;
  }
}
