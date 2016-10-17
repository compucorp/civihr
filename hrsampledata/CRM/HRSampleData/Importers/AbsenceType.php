<?php


/**
 * Class CRM_HRSampleData_Importers_AbsenceType
 *
 */
class CRM_HRSampleData_Importers_AbsenceType extends CRM_HRSampleData_DataImporter
{

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $isExist = $this->callAPI('HRAbsenceType', 'getcount', ['name' => $row['name']]);
    if (!$isExist) {
      $this->callAPI('HRAbsenceType', 'create', $row);
    }
  }

}
