<?php

/**
 * Class CRM_CiviHRSampleData_Importer_ContactEmail
 *
 */
class CRM_CiviHRSampleData_Importer_ContactEmail extends CRM_CiviHRSampleData_DataImporter
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `contact_id` & `email`
   */
  protected function insertRecord(array $row) {
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);
    $this->callAPI('Email', 'create', $row);
  }

}
