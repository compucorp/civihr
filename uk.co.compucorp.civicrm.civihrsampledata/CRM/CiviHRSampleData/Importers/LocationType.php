<?php

/**
 * Class CRM_CiviHRSampleData_Importer_LocationType
 *
 */
class CRM_CiviHRSampleData_Importer_LocationType extends CRM_CiviHRSampleData_DataImporter
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
