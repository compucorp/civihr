<?php

/**
 * Class CRM_HRSampleData_Cleaner_OptionValue
 */
class CRM_HRSampleData_Cleaner_OptionValue extends CRM_HRSampleData_CSVCleanerVisitor
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `option_group_id` & `name`
   */
  public function visit(array $row) {
    if ($row['option_group_id_type'] == 'title') {
      $row['option_group_id'] = $this->getOptionGroupNameByTitle($row['option_group_id']);
    }
    unset($row['option_group_id_type']);

    $this->deleteRecord(
      'OptionValue',
      ['name' => $row['name'], 'option_group_id' => $row['option_group_id']],
      $row['delete_on_uninstall']
    );
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
