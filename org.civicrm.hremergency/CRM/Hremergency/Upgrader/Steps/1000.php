<?php

trait CRM_Hremergency_Upgrader_Steps_1000 {

  /**
   * Upgrader to Create option values for 'Relationship with employee'
   * custom field.
   *
   * @return bool
   */
  public function upgrade_1000() {
    // relationship with employee options to create
    $relationships = ['Friend', 'Spouse', 'Partner'];

    // create required options
    foreach($relationships as $relationship) {
      if (!$this->up1000_isExistingOptionValue($relationship, 'relationship_with_employee_20150304120408')) {
        civicrm_api3('OptionValue', 'create', [
          'sequential' => 1,
          'option_group_id' => 'relationship_with_employee_20150304120408',
          'name' => $relationship,
          'label' => $relationship,
          'value' => strtolower($relationship),
          'is_active' => 1
        ]);
      }
    }

    return TRUE;
  }

  /**
   * Checks if given value already exists for the given option group.
   *
   * @param string $value
   *   Value to be checked
   * @param $groupName
   *   Name of group where the value should be searched
   *
   * @return bool
   *   true if value is found, false otherwise
   */
  private function up1000_isExistingOptionValue($value, $groupName) {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => $groupName,
      'name' => $value,
      'label' => $value,
      'value' => $value,
      'options' => [
        'limit' => 0,
        'or' => [
          ['label', 'name', 'value']
        ]
      ]
    ]);

    if ($result['count'] > 0) {
      return TRUE;
    }

    return FALSE;
  }
}
