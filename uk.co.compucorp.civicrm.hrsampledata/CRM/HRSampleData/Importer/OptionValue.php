<?php

/**
 * Class CRM_HRSampleData_Importer_OptionValue
 */
class CRM_HRSampleData_Importer_OptionValue extends CRM_HRSampleData_CSVImporterVisitor
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `option_group_id` & `name`
   */
  protected function importRecord(array $row) {
    if ($row['option_group_id_type'] == 'title') {
      $row['option_group_id'] = $this->getOptionGroupNameByTitle($row['option_group_id']);
    }
    unset($row['option_group_id_type']);
    $optionValueExists = $this->callAPI('OptionValue', 'getcount', [
      'option_group_id' => $row['option_group_id'],
      'name' => $row['name'],
    ]);

    if (!$optionValueExists) {
      $this->callAPI('OptionValue', 'create', $row);
    }
  }

  /**
   * Gets option group name by its title
   *
   * @param string $groupTitle
   */
  private function getOptionGroupNameByTitle($groupTitle) {
    $optionGroup = $this->callAPI( 'OptionGroup', 'get', [
        'sequential' => 1,
        'return' => ['name'],
        'title' => $groupTitle,
        'options' => ['limit' => 0],
      ]);

    return array_shift($optionGroup['values'])['name'];
  }

}
