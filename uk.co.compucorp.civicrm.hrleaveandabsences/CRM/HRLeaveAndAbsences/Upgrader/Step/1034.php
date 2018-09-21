<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1034 {

  /**
   * Adds the category field to the Absence Type table
   *
   * @return bool
   */
  public function upgrade_1034() {
    $absenceTypeTable = AbsenceType::getTableName();

    if(!SchemaHandler::checkIfFieldExists($absenceTypeTable, 'category')) {
      CRM_Core_DAO::executeQuery("
        ALTER TABLE {$absenceTypeTable}
        ADD category varchar(10) NOT NULL 
        COMMENT 'This is used for grouping leave types.'
      ");
    }

    return TRUE;
  }
}
