<?php

/**
 * Class CRM_HRSampleData_Importer_EmergencyContacts
 */
class CRM_HRSampleData_Importer_EmergencyContacts extends CRM_HRSampleData_Importer_CustomFields
{

  private $relationshipWithEmployeeTypes = [];

  public function __construct() {
    parent::__construct('Emergency_Contacts');

    $this->relationshipWithEmployeeTypes = $this->getFixData('OptionValue', 'name', 'value', [
      'option_group_id' => 'relationship_with_employee_20150304120408',
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function importRecord(array $row) {
    $row['entity_id'] = $this->getDataMapping('contact_mapping', $row['entity_id']);

    if (!empty($row['Relationship_with_Employee'])) {
      $row['Relationship_with_Employee'] = $this->relationshipWithEmployeeTypes[$row['Relationship_with_Employee']];
    }

    parent::importRecord($row);
  }
}
