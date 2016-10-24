<?php

/**
 * Class CRM_HRSampleData_Importer_ContactPhone
 *
 */
class CRM_HRSampleData_Importer_ContactPhone extends CRM_HRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `contact_id` & `phone`
   */
  protected function insertRecord(array $row) {
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);
    $this->callAPI('Phone', 'create', $row);
  }

}
