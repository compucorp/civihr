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
  protected function operate(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    parent::operate($row);
  }

}
