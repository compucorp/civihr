<?php

/**
 * Class CRM_CiviHRSampleData_Importer_VacancyStage
 *
 */
class CRM_CiviHRSampleData_Importer_CustomFields extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * Stores custom group columns (fields) IDs/Names
   *
   * @var array
   */
  protected $columns = [];

  public function __construct($customGroupName) {
    $this->columns = $this->getFixData('CustomField', 'name', 'id', [
      'custom_group_id' => $customGroupName,
    ]);
  }

  /**
   * {@inheritdoc}
   * Generic method to handle custom fields import
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $toInsert['entity_id'] = $row['entity_id'];

    foreach($row as $colName => $value) {
      if (!empty($value) && $colName != 'entity_id') {
        $columnName = 'custom_' . $this->columns[$colName];
        $toInsert[$columnName] = $value;
      }
    }

    $this->callAPI('CustomValue', 'create', $toInsert);
  }

}
