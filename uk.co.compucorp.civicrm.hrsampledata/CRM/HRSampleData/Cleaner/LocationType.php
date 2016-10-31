<?php

/**
 * Class CRM_HRSampleData_Cleaner_LocationType
 */
class CRM_HRSampleData_Cleaner_LocationType extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `name`
   */
  protected function operate(array $row) {
    $this->deleteRecord('LocationType', ['name' => $row['name']]);
  }

}
