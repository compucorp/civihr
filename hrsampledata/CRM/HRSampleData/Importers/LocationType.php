<?php


/**
 * Class CRM_HRSampleData_Importers_LocationType
 *
 */
class CRM_HRSampleData_Importers_LocationType extends CRM_HRSampleData_DataImporter
{

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row Should at least contain `name`
   */
  protected function insertRecord(array $row) {
    $isExist = $this->callAPI('LocationType', 'getcount', [
      'name' => $row['name'],
    ]);

    //  If there is no location type with the same name then create it
    if (!$isExist) {
      $this->callAPI('LocationType', 'create', $row);
    }
  }

}
