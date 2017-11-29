<?php

trait CRM_Hremergency_Upgrader_Steps_1003 {

  /**
   * Makes some emergency contact fields required.
   */
  public function upgrade_1003() {
    $params = ['custom_group_id' => 'Emergency_Contacts'];
    $customFields = civicrm_api3('CustomField', 'get', $params)['values'];

    $shouldBeRequired = [
      'Name',
      'Mobile_number',
      'Relationship_with_Employee',
      'Dependant_s_',
    ];

    foreach ($customFields as $customField) {
      if (!in_array($customField['name'], $shouldBeRequired)) {
        continue;
      }

      $customField['is_required'] = TRUE;
      civicrm_api3('CustomField', 'create', $customField);
    }

    return TRUE;
  }
  
}
