<?php

trait CRM_HRCore_Upgrader_Steps_1021 {
  
  /**
   * Hide Fields For Contact Summary
   */
  public function upgrade_1021() {
    $valuesRemoved = $this->up1021_getContactEditOptionValues();
    $optionValues = $this->up1021_getActiveContactEditOptionValues();
    
    $activeOptionValues = array_diff($optionValues, $valuesRemoved);
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
  private function up1021_getActiveContactEditOptionValues() {
    $result = civicrm_api3('Setting', 'get', [
      'sequential' => 1,
      'return' => ['contact_edit_options'],
    ]);
    
    return $result['values'][0]['contact_edit_options'];
  }
  
  /**
   * Retrieves value for contact edit options to be removed
   *
   * @return array
   */
  private function up1021_getContactEditOptionValues() {
    $values = [];
    $names = $this->up1021_getUnusedContactEditOptionNames();
    
    $params = [
      'sequential' => 1,
      'return' => ['value', 'name'],
      'option_group_id' => 'contact_edit_options',
      'is_active' => 1,
      'name' => ['IN' => $names]
    ];
    
    $optionValues = civicrm_api3('OptionValue', 'get', $params);
    foreach ($optionValues['values'] as $optionValue) {
      array_push($values, $optionValue['value']);
    }
  
    return $values;
  }
  
  /**
   * Get names of contact edit options not in use
   *
   * @return array
   */
  private function up1021_getUnusedContactEditOptionNames() {
    $names = [];
    
    foreach (['Contact', 'IM', 'Website'] as $name) {
      $params = ['return' => ['id']];
      if ($name == 'Contact') {
        $params['suffix_id'] = ['IS NOT NULL' => 1];
      }
      
      $result = civicrm_api3($name, 'get', $params);
      if ($result['count'] == 0) {
        array_push($names, $name == 'Contact' ? 'Suffix' : $name);
      }
    }
    
    return $names;
  }
  
}
