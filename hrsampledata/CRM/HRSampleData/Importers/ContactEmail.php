<?php


/**
 * Class CRM_HRSampleData_Importers_ContactEmail
 *
 */
class CRM_HRSampleData_Importers_ContactEmail extends CRM_HRSampleData_DataImporter
{

  /**
   * @see CRM_HRSampleData_DataImporter::insertRecord
   * @param array $row Should at least contain `contact_id` & `email`
   */
  protected function insertRecord(array $row) {
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);
    $this->callAPI('Email', 'create', $row);
  }

}
