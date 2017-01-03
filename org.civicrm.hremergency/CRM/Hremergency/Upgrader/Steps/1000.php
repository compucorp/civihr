<?php

trait CRM_Hremergency_Upgrader_Steps_1000 {

  public function upgrade_1000() {
    $relationships = ['Friend', 'Spouse', 'Partner'];
    $this->up1000_createRelationshipsWithEmployeeOptions($relationships);

    return TRUE;
  }

  /**
   * Creates option values for 'Relationship with employee'
   * custom field.
   *
   * @param array $relationships
   *   A list of relationship with employee option values to create
   */
  private function up1000_createRelationshipsWithEmployeeOptions($relationships) {
    $groupName= civicrm_api3('CustomField', 'get', [
      'sequential' => 1,
      'return' => array("column_name"),
      'name' => "Relationship_with_Employee",
      'options' => ['limit' => 0]
    ]);

    if (!empty($groupName['values']['column_name'])) {
      $groupName = $groupName['values']['column_name'];

      foreach($relationships as $relationship) {
        civicrm_api3('OptionValue', 'create', array(
          'sequential' => 1,
          'option_group_id' => $groupName,
          'name' => $relationship,
          'label' => $relationship,
          'is_active' => 1,
        ));
      }
    }
  }

}