<?php

/**
 * Class CRM_HRSampleData_Importer_ContactPhone
 */
class CRM_HRSampleData_Importer_ContactPhone extends CRM_HRSampleData_CSVImporterVisitor
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `contact_id` & `phone`
   */
  protected function importRecord(array $row) {
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);
    $this->callAPI('Phone', 'create', $row);
  }

}
