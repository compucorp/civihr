<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1006 {

  /**
   * Adds the calculation_unit field to the Absence Type table
   * and also updates empty calculation_unit columns to be set to
   * days unit.
   *
   * @return bool
   */
  public function upgrade_1006() {
    $absenceTypeTable = AbsenceType::getTableName();

    if(!SchemaHandler::checkIfFieldExists($absenceTypeTable, 'calculation_unit')) {
      CRM_Core_DAO::executeQuery("
        ALTER TABLE {$absenceTypeTable} 
        ADD calculation_unit varchar(512) NOT NULL   
        COMMENT 'One of the values of the Absence type calculation units option group'");

      CRM_Core_DAO::executeQuery("
        UPDATE {$absenceTypeTable}
        SET calculation_unit = 1 WHERE calculation_unit = ''");
    }

    return true;
  }
}
