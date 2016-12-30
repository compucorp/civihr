<?php

trait CRM_HRCore_Upgrader_Steps_1001 {

  public function upgrade_1001() {
    $listsToDelete = [
      ['civicrm_contact_Type', 'name', 'civicrmContactTypesList'],
      ['civicrm_case_type', 'name', 'civicrmCaseTypesList'],
      ['civicrm_relationship_type', 'name_b_a', 'civicrmRelationshipTypesList'],
      ['civicrm_option_value', 'name', 'civicrmActivityTypesList'],
      ['civicrm_location_type', 'name', 'civicrmLocationTypesList'],
    ];

    foreach ($listsToDelete as $list) {
      $this->listDelete($list[0], $list[1], $list[2]);
    }

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
  private function listDelete($table, $uniqueField, $listCallback) {
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
   * A list of sample CiviCRM contact types which need to be removed.
   *
   * @return array
   */
  private function civicrmContactTypesList() {
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
  private function civicrmCaseTypesList() {
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
  private function civicrmRelationshipTypesList() {
    return [
      'Benefits Specialist',
      'Case Coordinator',
      'Parent of',
      'Health Services Coordinator',
      'Homeless Services Coordinator',
      'Partner of',
      'Senior Services Coordinator',
      'Sibling of',
      'Spouse of',
      'Supervisor',
      'Volunteer is',
    ];
  }

  /**
   * A list of sample CiviCRM Activity types which need to be removed.
   *
   * @return array
   */
  private function civicrmActivityTypesList() {
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
  private function civicrmLocationTypesList() {
    return [
      'Main',
      'Other',
    ];
  }

}