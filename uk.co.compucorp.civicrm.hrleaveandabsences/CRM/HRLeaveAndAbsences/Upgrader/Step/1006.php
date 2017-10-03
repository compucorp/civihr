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

      $daysUnitValue = $this->getDaysUnitValue();
      CRM_Core_DAO::executeQuery("
        UPDATE {$absenceTypeTable}
        SET calculation_unit = {$daysUnitValue} WHERE calculation_unit = ''");
    }

    return true;
  }

  /**
   * Get the days option value of the calculation unit option group.
   *
   * @return mixed
   */
  private function getDaysUnitValue() {
    $calculationUnitOptions = array_flip(AbsenceType::buildOptions('calculation_unit', 'validate'));

    return $calculationUnitOptions['days'];
  }
}
