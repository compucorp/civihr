<?php

/**
 * Class CRM_HRSampleData_Importer_OptionValue
 *
 */
class CRM_HRSampleData_Importer_OptionValue extends CRM_HRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `option_group_id` & `name`
   */
  protected function insertRecord(array $row) {
    $optionValueExists = $this->callAPI('OptionValue', 'getcount', [
      'option_group_id' => $row['option_group_id'],
      'name' => $row['name'],
    ]);

    if (!$optionValueExists) {
      $this->callAPI('OptionValue', 'create', $row);
    }
  }

}
