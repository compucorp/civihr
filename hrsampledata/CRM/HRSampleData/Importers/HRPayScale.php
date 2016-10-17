<?php


/**
 * Class CRM_HRSampleData_Importers_HRPayScale
 *
 */
class CRM_HRSampleData_Importers_HRPayScale extends CRM_HRSampleData_DataImporter
{

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $isExist = $this->callAPI('HRPayScale', 'getcount', $row);
    if (!$isExist) {
      $this->callAPI('HRPayScale', 'create', $row);
    }
  }

}
