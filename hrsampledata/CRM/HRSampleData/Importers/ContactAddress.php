<?php


/**
 * Class CRM_HRSampleData_Importers_ContactAddress
 *
 */
class CRM_HRSampleData_Importers_ContactAddress extends CRM_HRSampleData_DataImporter
{

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row Should at least contain `contact_id` & `location_type_id`
   */
  protected function insertRecord(array $row) {
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);
    $this->callAPI('Address', 'create', $row);
  }

}
