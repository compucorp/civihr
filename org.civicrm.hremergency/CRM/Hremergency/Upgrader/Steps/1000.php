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

    // fetch 'Relationship with employee' option group name
    $groupName= civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'return' => array("column_name"),
      'name' => "Relationship_with_Employee",
      'options' => ['limit' => 1]
    ]);

    // create required options
    if (!empty($groupName['values']['column_name'])) {
      $groupName = $groupName['values']['column_name'];

      foreach($relationships as $relationship) {
        civicrm_api3('OptionValue', 'create', [
          'sequential' => 1,
          'option_group_id' => $groupName,
          'name' => $relationship,
          'label' => $relationship,
          'is_active' => 1,
        ]);
      }
    }

    return TRUE;
  }

}