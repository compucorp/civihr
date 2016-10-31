<?php

/**
 * Class CRM_HRSampleData_Cleaner_AbsenceType
 *
 */
class CRM_HRSampleData_Cleaner_AbsenceType extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   */
  protected function operate(array $row) {
    $this->deleteRecord('HRAbsenceType', ['name' => $row['name']]);
  }

}
