<?php

trait CRM_HRUI_Upgrader_Steps_4701 {
  /**
   * Adds Custom Inline Data group for fields to be shown within contact details
   * and a NI / SSN field alphanumeric field for that group.
   */
  public function upgrade_4701() {
    // Add Inline Custom Group
    $customGroupResult = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'name' => 'Inline_Custom_Data'
    ]);

    if ($customGroupResult['count'] < 1) {
      $groupData = [
        'sequential' => 1,
        'title' => 'Inline Custom Data',
        'name' => 'Inline_Custom_Data',
        'extends' => ['0' => 'Individual'],
        'weight' => 21,
        'collapse_display' => 1,
        'style' => 'Inline',
        'is_active' => 1
      ];
      $customGroupResult = civicrm_api3('CustomGroup', 'create', $groupData);
    }
    $inlineCustomGroup = array_shift($customGroupResult['values']);

    // Add NI/SSN Field
    $fieldData = [
      'sequential' => 1,
      'custom_group_id' => $inlineCustomGroup['id'],
      'name' => 'NI_SSN',
      'label' => 'NI / SSN',
      'html_type' => 'Text',
      'data_type' => 'String',
      'weight' => 1,
      'is_required' => 0,
      'is_searchable' => 1,
      'is_active' => 1
    ];
    $createResult = civicrm_api3('CustomField', 'create', $fieldData);
    $niSSNField = array_shift($createResult['values']);

    $identTableName = $this->up4701_getIdentTableName();
    $identFieldName = $this->up4701_getIdentFieldName();

    $isEnabled = _hrui_is_extension_enabled('org.civicrm.hrident');

    if (!$isEnabled) {
      return TRUE;
    }

    $query = "
      UPDATE {$inlineCustomGroup['table_name']}, $identTableName
         SET {$niSSNField['column_name']} = $identFieldName
       WHERE {$inlineCustomGroup['table_name']}.entity_id = $identTableName.entity_id
         AND is_government = 1
    ";
    CRM_Core_DAO::executeQuery($query);

    return true;
  }

  private function up4701_getIdentTableName() {
    $customGroupResult = civicrm_api3('CustomGroup', 'get', [
      'sequential' => 1,
      'name' => 'Identify'
    ]);

    if ($customGroupResult['count'] > 0) {
      return $customGroupResult['values'][0]['table_name'];
    }
  }

  private function up4701_getIdentFieldName() {
    $customFieldResult = civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'name' => 'Number'
    ]);

    if ($customFieldResult['count'] > 0) {
      return $customFieldResult['values'][0]['column_name'];
    }
  }

}
