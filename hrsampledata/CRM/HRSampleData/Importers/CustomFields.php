<?php


/**
 * Class CRM_HRSampleData_Importers_VacancyStage
 *
 */
class CRM_HRSampleData_Importers_CustomFields extends CRM_HRSampleData_DataImporter
{

  /**
   * @var array To store columns IDs/Names
   */
  protected $columns =[];

  public function __construct($customGroupName) {
    $this->columns = $this->getFixData('CustomField', 'name', 'id', [
      'custom_group_id' => $customGroupName,
    ]);
  }

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
