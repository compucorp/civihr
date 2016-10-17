<?php


/**
 * Class CRM_CiviHRSampleData_Importers_HRHoursLocation
 *
 */
class CRM_CiviHRSampleData_Importers_HRHoursLocation extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * @see CRM_CiviHRSampleData_DataImporter::insertRecord
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $isExist= $this->callAPI('HRHoursLocation', 'getcount', $row);
    if (!$isExist) {
      $this->callAPI('HRHoursLocation', 'create', $row);
    }
  }

}
