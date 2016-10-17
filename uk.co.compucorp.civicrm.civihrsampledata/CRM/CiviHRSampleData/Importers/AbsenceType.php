<?php


/**
 * Class CRM_CiviHRSampleData_Importers_AbsenceType
 *
 */
class CRM_CiviHRSampleData_Importers_AbsenceType extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * @see CRM_CiviHRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $isExist = $this->callAPI('HRAbsenceType', 'getcount', ['name' => $row['name']]);
    if (!$isExist) {
      $this->callAPI('HRAbsenceType', 'create', $row);
    }
  }

}
