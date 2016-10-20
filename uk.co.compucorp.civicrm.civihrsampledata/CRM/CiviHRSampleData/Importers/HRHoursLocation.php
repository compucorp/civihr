<?php

/**
 * Class CRM_CiviHRSampleData_Importer_HRHoursLocation
 *
 */
class CRM_CiviHRSampleData_Importer_HRHoursLocation extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $hourLocationExists = $this->callAPI('HRHoursLocation', 'getcount', $row);
    if (!$hourLocationExists) {
      $this->callAPI('HRHoursLocation', 'create', $row);
    }
  }

}
