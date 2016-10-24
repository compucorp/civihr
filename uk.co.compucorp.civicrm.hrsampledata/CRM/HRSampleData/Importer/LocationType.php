<?php

/**
 * Class CRM_HRSampleData_Importer_LocationType
 *
 */
class CRM_HRSampleData_Importer_LocationType extends CRM_HRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `name`
   */
  protected function insertRecord(array $row) {
    $LocationTypeExists = $this->callAPI('LocationType', 'getcount', [
      'name' => $row['name'],
    ]);

    if (!$LocationTypeExists) {
      $this->callAPI('LocationType', 'create', $row);
    }
  }

}
