<?php

/**
 * Class CRM_HRSampleData_Importer_ContactEmail
 */
class CRM_HRSampleData_Importer_ContactEmail extends CRM_HRSampleData_CSVHandler
{

  /**
   * {@inheritdoc}
   *
   * @param array $row
   *   Should at least contain `contact_id` & `email`
   */
  protected function operate(array $row) {
    $row['contact_id'] = $this->getDataMapping('contact_mapping', $row['contact_id']);
    $this->callAPI('Email', 'create', $row);
  }

}
