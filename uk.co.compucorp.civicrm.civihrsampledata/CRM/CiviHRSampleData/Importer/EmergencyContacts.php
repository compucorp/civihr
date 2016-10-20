<?php

/**
 * Class CRM_CiviHRSampleData_Importer_EmergencyContacts
 *
 */
class CRM_CiviHRSampleData_Importer_EmergencyContacts extends CRM_CiviHRSampleData_Importer_CustomFields
{

  public function __construct() {
    parent::__construct('Emergency_Contacts');
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
