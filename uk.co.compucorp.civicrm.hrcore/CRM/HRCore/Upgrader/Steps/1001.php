<?php

trait CRM_HRCore_Upgrader_Steps_1001 {

  public function upgrade_1001() {
    $listsToDelete = [
      ['civicrm_contact_Type', 'name', 'up1001_civicrmContactTypesList'],
      ['civicrm_case_type', 'name', 'up1001_civicrmCaseTypesList'],
      ['civicrm_relationship_type', 'name_b_a', 'up1001_civicrmRelationshipTypesList'],
      ['civicrm_option_value', 'name', 'up1001_civicrmActivityTypesList'],
      ['civicrm_location_type', 'name', 'up1001_civicrmLocationTypesList'],
      ['civicrm_option_value', 'name', 'up1001_civicrmMobileProvidersList'],
    ];

    foreach ($listsToDelete as $list) {
      $this->up1001_listDelete($list[0], $list[1], $list[2]);
    }

    $this->up1001_deleteEthnicityOptions();

    CRM_Core_BAO_Navigation::resetNavigation();

    return TRUE;
  }

  /**
   * Removes a list of options (e.g case types, option values..) from a specific table
   * based on a specific unique key in the table
   *
   * @param string $table
   *   The table that contains the data that we want to remove
   * @param string $uniqueField
   *   A name of unique key in that table that we want to
   *   use in order to match and delete the list items
   * @param string $listCallback
   *   The name of the method that is used to get the option
   *   list that we need to delete its items, The callback
   *   must return an array.
   */
  private function up1001_listDelete($table, $uniqueField, $listCallback) {
    if (is_callable(array($this, $listCallback))) {
      $toDelete = $this->{$listCallback}();
    }

    if (!empty($toDelete)) {
      $toDelete = implode("','", $toDelete);

      $sql = "DELETE FROM `{$table}` WHERE {$uniqueField} IN  ('{$toDelete}')";

      CRM_Core_DAO::executeQuery($sql);
    }
  }

  /**
   * Removes a list of unneeded ethnicity options
   */
  private function up1001_deleteEthnicityOptions() {
    $ethnicityGroupID = civicrm_api3('CustomField', 'get', array(
      'sequential' => 1,
      'return' => array("option_group_id"),
      'name' => "ethnicity",
      'options' => ['limit' => 0]
    ))['id'];

    $ethnicityOptions = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'option_group_id' => $ethnicityGroupID,
    ));

    if (!empty($ethnicityOptions['values'])) {
      foreach ($ethnicityOptions['values'] as $option) {
        if (!in_array($option['name'], ['Prefer_Not_to_Say', 'Not_Applicable'])) {
          civicrm_api3('OptionValue', 'delete', array(
            'id' => $option['id'],
          ));
        }
      }
    }

  }


  /**
   * A list of sample CiviCRM contact types which need to be removed.
   *
   * @return array
   */
  private function up1001_civicrmContactTypesList() {
    return [
      'Student',
      'Parent',
      'Staff',
      'Team',
      'Sponsor',
    ];
  }

  /**
   * A list of sample CiviCRM case types which need to be removed.
   *
   * @return array
   */
  private function up1001_civicrmCaseTypesList() {
    return [
      'adult_day_care_referral',
      'housing_support'
    ];
  }

  /**
   * A list of sample CiviCRM relationship types  which need to be removed.
   *
   * @return array
   */
  private function up1001_civicrmRelationshipTypesList() {
    return [
      'Benefits Specialist',
      'Case Coordinator',
      'Parent of',
      'Health Services Coordinator',
      'Homeless Services Coordinator',
      'Senior Services Coordinator',
      'Sibling of',
      'Supervisor',
      'Volunteer is',
      'Spouse of',
      'Partner of',
    ];
  }

  /**
   * A list of sample CiviCRM Activity types which need to be removed.
   *
   * @return array
   */
  private function up1001_civicrmActivityTypesList() {
    return [
      'Medical evaluation',
      'Mental health evaluation',
      'Secure temporary housing',
      'Income and benefits stabilization',
      'Long-term housing plan',
      'ADC referral',
    ];
  }

  /**
   * A list of sample CiviCRM Location types which need to be removed.
   *
   * @return array
   */
  private function up1001_civicrmLocationTypesList() {
    return [
      'Main',
      'Other',
    ];
  }

  /**
   * A list of sample CiviCRM mobile providers which need to be removed.
   *
   * @return array
   */
  private function up1001_civicrmMobileProvidersList() {
    $mobileProviders = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'option_group_id' => "mobile_provider",
      'options' => array('limit' => 0),
    ));

    $providers = [];
    if (!empty($mobileProviders['values'])) {
      $providers = array_column($mobileProviders['values'], 'name');
    }

    return $providers;
  }

}