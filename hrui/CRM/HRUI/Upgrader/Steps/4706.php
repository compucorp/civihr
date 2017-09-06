<?php

trait CRM_HRUI_Upgrader_Steps_4706 {

  /**
   * Adds "is applying for NI/SSN" field
   */
  public function upgrade_4706() {
    $groupName = 'Inline_Custom_Data';
    $customGroup = civicrm_api3('CustomGroup', 'get', ['name' => $groupName]);

    if ($customGroup['count'] != 1) {
      throw new \Exception("Cannot continue without Inline_Custom_Data group");
    }

    $customGroup = array_shift($customGroup['values']);

    // Add "is applying" field
    $fieldData = [
      'custom_group_id' => $customGroup['id'],
      'name' => 'has_applied_for_identification',
      'label' => 'Applied for NI/SSN',
      'html_type' => 'Radio',
      'data_type' => 'Boolean',
      'weight' => 2,
      'is_required' => 0,
      'is_searchable' => 1,
      'is_active' => 1
    ];
    civicrm_api3('CustomField', 'create', $fieldData);

    return TRUE;
  }

}
