<?php

/**
 * Class CRM_HRSampleData_Cleaner_HRHoursLocation
 *
 */
class CRM_HRSampleData_Cleaner_HRHoursLocation extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   */
  protected function operate(array $row) {
    $this->deleteRecord('HRHoursLocation', ['location' => $row['location']]);
  }

}
