<?php


/**
 * Class CRM_HRSampleData_Importers_BankDetails
 *
 */
class CRM_HRSampleData_Importers_BankDetails extends CRM_HRSampleData_Importers_CustomFields
{

  public function __construct() {
    parent::__construct('Bank_Details');
  }

  protected function insertRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    parent::insertRecord($row);
  }

}
