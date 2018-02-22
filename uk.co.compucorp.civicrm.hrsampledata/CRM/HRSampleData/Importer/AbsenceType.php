<?php

use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * Class CRM_HRSampleData_Importer_AbsenceType
 */
class CRM_HRSampleData_Importer_AbsenceType extends CRM_HRSampleData_CSVImporterVisitor {

  public function __construct() {
    $this->removeAllAbsenceTypes();
  }

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $currentID = $this->unsetArrayElement($row, 'id');
    $absenceType = $this->callAPI('AbsenceType', 'create', $row);
    $this->setDataMapping('absence_type_mapping', $currentID, $absenceType['id']);
  }

  /**
   * Removes existing absence types.
   */
  private function removeAllAbsenceTypes() {
    $absenceTypeTable = AbsenceType::getTableName();

    // It's not possible to use the API to delete the absence types
    // because some of them are reserved and these cannot be deleted
    // with the API
    CRM_Core_DAO::executeQuery("DELETE FROM {$absenceTypeTable}");
  }

}
