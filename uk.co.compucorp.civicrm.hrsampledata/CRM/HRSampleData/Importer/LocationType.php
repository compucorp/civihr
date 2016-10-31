<?php

/**
 * Class CRM_HRSampleData_Importer_LocationType
 */
class CRM_HRSampleData_Importer_LocationType extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `name`
   */
  protected function operate(array $row) {
    $locationTypeExists = $this->callAPI('LocationType', 'getcount', [
      'name' => $row['name'],
    ]);

    if (!$locationTypeExists) {
      $this->callAPI('LocationType', 'create', $row);
    }
  }

}
