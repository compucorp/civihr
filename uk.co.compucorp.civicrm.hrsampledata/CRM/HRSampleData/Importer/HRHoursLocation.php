<?php

/**
 * Class CRM_HRSampleData_Importer_HRHoursLocation
 *
 */
class CRM_HRSampleData_Importer_HRHoursLocation extends CRM_HRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   */
  protected function insertRecord(array $row) {
    $hourLocationExists = $this->callAPI('HRHoursLocation', 'getcount', $row);
    if (!$hourLocationExists) {
      $this->callAPI('HRHoursLocation', 'create', $row);
    }
  }

}
