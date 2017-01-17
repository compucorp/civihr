<?php

/**
 * Class CRM_HRSampleData_Cleaner_HRPayScale
 */
class CRM_HRSampleData_Cleaner_HRPayScale extends CRM_HRSampleData_CSVCleanerVisitor
{

  /**
   * {@inheritdoc}
   */
  public function visit(array $row) {
    $this->deleteRecord('HRPayScale', ['pay_scale' => $row['pay_scale']]);
  }

}
