<?php

/**
 * Class CRM_HRSampleData_Importer_EmergencyContacts
 */
class CRM_HRSampleData_Importer_EmergencyContacts extends CRM_HRSampleData_Importer_CustomFields
{

  public function __construct() {
    parent::__construct('Emergency_Contacts');
  }

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    parent::importRecord($row);
  }

}
