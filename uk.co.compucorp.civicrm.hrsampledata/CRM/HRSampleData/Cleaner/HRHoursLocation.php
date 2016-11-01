<?php

/**
 * Class CRM_HRSampleData_Cleaner_HRHoursLocation
 */
class CRM_HRSampleData_Cleaner_HRHoursLocation extends CRM_HRSampleData_CSVCleanerVisitor
{

  /**
   * {@inheritdoc}
   */
  public function visit(array $row) {
    $this->deleteRecord('HRHoursLocation', ['location' => $row['location']]);
  }

}
