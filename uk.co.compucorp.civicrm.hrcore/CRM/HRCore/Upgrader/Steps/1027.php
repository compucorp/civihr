<?php

trait CRM_HRCore_Upgrader_Steps_1027 {

  /**
   * Hide Fields For Contact Summary
   *
   * @return bool
   */
  public function upgrade_1027() {
    $valuesToRemove = $this->up1027_getContactEditOptionValuesToRemove();
    $activeOptionValues = $this->up1027_getActiveContactEditOptionValues();

    $activeOptionValues = array_diff($activeOptionValues, $valuesToRemove);
    civicrm_api3('Setting', 'create', [
      'contact_edit_options' => $activeOptionValues,
    ]);

    return TRUE;
  }

  /**
   * Retrieves active contact edit option setting value
   *
   * @return array
   */
  private function up1027_getActiveContactEditOptionValues() {
    $result = civicrm_api3('Setting', 'get', [
      'return' => ['contact_edit_options'],
    ]);

    return $result['values'][$result['id']]['contact_edit_options'];
  }

  /**
   * Retrieves value for contact edit options to be removed
   *
   * @return array
   */
  private function up1027_getContactEditOptionValuesToRemove() {
    $values = [];
    $names = $this->up1027_getUnusedContactEditOptionNames();

    $params = [
      'return' => ['value', 'name'],
      'option_group_id' => 'contact_edit_options',
      'is_active' => 1,
      'name' => ['IN' => $names]
    ];

    $results = civicrm_api3('OptionValue', 'get', $params);
    foreach ($results['values'] as $result) {
      array_push($values, $result['value']);
    }

    return $values;
  }

  /**
   * Get names of contact edit options not in use
   *
   * @return array
   */
  private function up1027_getUnusedContactEditOptionNames() {
    $names = [];

    foreach (['Contact', 'IM', 'Website'] as $name) {
      $params = ['return' => ['id']];
      if ($name === 'Contact') {
        $params['suffix_id'] = ['IS NOT NULL' => 1];
      }

      $result = civicrm_api3($name, 'get', $params);
      if ($result['count'] == 0) {
        array_push($names, $name === 'Contact' ? 'Suffix' : $name);
      }
    }

    return $names;
  }

}
