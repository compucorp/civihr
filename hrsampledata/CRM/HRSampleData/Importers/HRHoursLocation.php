<?php


/**
 * Class CRM_HRSampleData_Importers_HRHoursLocation
 *
 */
class CRM_HRSampleData_Importers_HRHoursLocation extends CRM_HRSampleData_DataImporter
{

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $isExist= $this->callAPI('HRHoursLocation', 'getcount', $row);
    if (!$isExist) {
      $this->callAPI('HRHoursLocation', 'create', $row);
    }
  }

}
