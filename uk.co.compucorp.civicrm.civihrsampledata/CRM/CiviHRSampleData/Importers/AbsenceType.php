<?php

/**
 * Class CRM_CiviHRSampleData_Importer_AbsenceType
 *
 */
class CRM_CiviHRSampleData_Importer_AbsenceType extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $absenceTypeExists = $this->callAPI('HRAbsenceType', 'getcount', ['name' => $row['name']]);
    if (!$absenceTypeExists) {
      $this->callAPI('HRAbsenceType', 'create', $row);
    }
  }

}
