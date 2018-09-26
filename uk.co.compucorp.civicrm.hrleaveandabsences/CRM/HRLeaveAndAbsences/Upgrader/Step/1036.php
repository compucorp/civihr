<?php

use CRM_Core_BAO_SchemaHandler as SchemaHandler;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceTYpe;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1036 {

  /**
   * Adds the subtype field to the Absence Type table
   *
   * @return bool
   */
  public function upgrade_1036() {
    $absenceTypeTable = AbsenceType::getTableName();

    if (!SchemaHandler::checkIfFieldExists($absenceTypeTable, 'subtype')) {
      CRM_Core_DAO::executeQuery("
          ALTER TABLE {$absenceTypeTable}
          ADD subtype VARCHAR(10)
          COMMENT 'This helps to further group custom leave type.'
      ");
    }

    return TRUE;
  }
}
