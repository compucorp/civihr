<?php

/**
 * Class CRM_HRSampleData_Cleaner_AbsenceType
 */
class CRM_HRSampleData_Cleaner_AbsenceType extends CRM_HRSampleData_CSVCleanerVisitor
{

  /**
   * {@inheritdoc}
   */
  public function visit(array $row) {
    $this->deleteRecord('HRAbsenceType', ['name' => $row['name']]);
  }

}
