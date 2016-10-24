<?php

/**
 * Class CRM_HRSampleData_Importer_HRPayScale
 *
 */
class CRM_HRSampleData_Importer_HRPayScale extends CRM_HRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $payScaleExists = $this->callAPI('HRPayScale', 'getcount', $row);
    if (!$payScaleExists) {
      $this->callAPI('HRPayScale', 'create', $row);
    }
  }

}
