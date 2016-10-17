<?php


/**
 * Class CRM_CiviHRSampleData_Importers_HRPayScale
 *
 */
class CRM_CiviHRSampleData_Importers_HRPayScale extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * @see CRM_CiviHRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $isExist = $this->callAPI('HRPayScale', 'getcount', $row);
    if (!$isExist) {
      $this->callAPI('HRPayScale', 'create', $row);
    }
  }

}
