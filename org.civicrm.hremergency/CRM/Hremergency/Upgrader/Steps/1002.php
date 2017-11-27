<?php

trait CRM_Hremergency_Upgrader_Steps_1002 {

  /**
   * Creates a new option value for emergency contact relationship.
   */
  public function upgrade_1002() {
    $this->up1002_create_emergency_contact_relationship_option();

    return TRUE;
  }

  /**
   * Creates the new option value "Son/Daughter" for emergency contacts
   * relationship with employee.
   */
  private function up1002_create_emergency_contact_relationship_option() {
    $relationshipName = 'Son/Daughter';
    $optionGroupName = 'relationship_with_employee_20150304120408';
    $params = [
      'name' => $relationshipName,
      'option_group_id' => $optionGroupName,
    ];
    $result = civicrm_api3('OptionValue', 'get', $params);

    if ($result['count'] != 0) {
      return;
    }

    $params['label'] = $relationshipName;
    $params['value'] = 'son_daughter';
    civicrm_api3('OptionValue', 'create', $params);
  }

}
