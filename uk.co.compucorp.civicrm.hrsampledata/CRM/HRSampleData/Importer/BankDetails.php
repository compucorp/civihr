<?php

/**
 * Class CRM_HRSampleData_Importer_BankDetails
 */
class CRM_HRSampleData_Importer_BankDetails extends CRM_HRSampleData_Importer_CustomFields
{

  public function __construct() {
    parent::__construct('Bank_Details');
  }

  /**
   * {@inheritdoc}
   */
  public function visit(array $row) {
    $this->importRecord($row);
  }

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    parent::importRecord($row);
  }

}
