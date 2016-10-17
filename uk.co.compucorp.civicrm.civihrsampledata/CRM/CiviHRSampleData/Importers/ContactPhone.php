<?php


/**
 * Class CRM_CiviHRSampleData_Importers_ContactPhone
 *
 */
class CRM_CiviHRSampleData_Importers_ContactPhone extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * @see CRM_CiviHRSampleData_DataImporter::insertRecord
   * @param array $row Should at least contain `contact_id` & `phone`
   */
  protected function insertRecord(array $row) {
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);
    $this->callAPI('Phone', 'create', $row);
  }

}
