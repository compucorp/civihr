<?php


/**
 * Class CRM_HRSampleData_Importers_OptionValue
 *
 */
class CRM_HRSampleData_Importers_OptionValue extends CRM_HRSampleData_DataImporter
{

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row Should at least contain `option_group_id` & `name`
   */
  protected function insertRecord(array $row) {
    $isExist = $this->callAPI('OptionValue', 'getcount', [
      'option_group_id' => $row['option_group_id'],
      'name' => $row['name'],
    ]);

    //  If there is no option value with the same name then create it
    if (!$isExist) {
      $this->callAPI('OptionValue', 'create', $row);
    }
  }

}
