<?php

/**
 * Class CRM_HRSampleData_Cleaner_LocationType
 */
class CRM_HRSampleData_Cleaner_LocationType extends CRM_HRSampleData_CSVCleanerVisitor
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `name`
   */
  public function visit(array $row) {
    $this->deleteRecord('LocationType', ['name' => $row['name']]);
  }

}
