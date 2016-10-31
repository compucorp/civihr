<?php

/**
 * Class CRM_HRSampleData_Cleaner_OptionValue
 */
class CRM_HRSampleData_Cleaner_OptionValue extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `option_group_id` & `name`
   */
  protected function operate(array $row) {
    $this->deleteRecord(
      'OptionValue',
      ['name' => $row['name'], 'option_group_id' => $row['option_group_id']]
    );
  }

}
