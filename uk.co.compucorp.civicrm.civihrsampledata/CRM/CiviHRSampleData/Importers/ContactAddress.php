<?php


/**
 * Class CRM_CiviHRSampleData_Importers_ContactAddress
 *
 */
class CRM_CiviHRSampleData_Importers_ContactAddress extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * @see CRM_CiviHRSampleData_DataImporter::insertRecord
   * @param array $row Should at least contain `contact_id` & `location_type_id`
   */
  protected function insertRecord(array $row) {
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);
    $this->callAPI('Address', 'create', $row);
  }

}
