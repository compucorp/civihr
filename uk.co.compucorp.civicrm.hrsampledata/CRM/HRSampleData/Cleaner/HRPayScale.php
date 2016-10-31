<?php

/**
 * Class CRM_HRSampleData_Cleaner_HRPayScale
 */
class CRM_HRSampleData_Cleaner_HRPayScale extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   */
  protected function operate(array $row) {
    $this->deleteRecord('HRPayScale', ['pay_scale' => $row['pay_scale']]);
  }

}
