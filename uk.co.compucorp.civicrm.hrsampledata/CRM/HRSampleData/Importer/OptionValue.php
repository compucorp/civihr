<?php

/**
 * Class CRM_HRSampleData_Importer_OptionValue
 */
class CRM_HRSampleData_Importer_OptionValue extends CRM_HRSampleData_CSVImporterVisitor
{

  /**
   * {@inheritdoc}
   */
  public function visit(array $row) {
    $this->importRecord($row);
  }

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `option_group_id` & `name`
   */
  protected function importRecord(array $row) {
    $optionValueExists = $this->callAPI('OptionValue', 'getcount', [
      'option_group_id' => $row['option_group_id'],
      'name' => $row['name'],
    ]);

    if (!$optionValueExists) {
      $this->callAPI('OptionValue', 'create', $row);
    }
  }

}
