<?php

/**
 * Class CRM_HRSampleData_Importer_ContactAddress
 *
 */
class CRM_HRSampleData_Importer_ContactAddress extends CRM_HRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `contact_id` & `location_type_id`
   */
  protected function insertRecord(array $row) {
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);
    $this->callAPI('Address', 'create', $row);
  }

}
