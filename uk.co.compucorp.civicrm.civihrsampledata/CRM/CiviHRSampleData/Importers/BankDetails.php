<?php

/**
 * Class CRM_CiviHRSampleData_Importer_BankDetails
 *
 */
class CRM_CiviHRSampleData_Importer_BankDetails extends CRM_CiviHRSampleData_Importer_CustomFields
{

  public function __construct() {
    parent::__construct('Bank_Details');
  }

  /**
   * {@inheritdoc}
   *
   * @param array $row
   */
  protected function insertRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    parent::insertRecord($row);
  }

}
