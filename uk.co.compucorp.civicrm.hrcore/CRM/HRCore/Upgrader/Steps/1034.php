<?php

trait CRM_HRCore_Upgrader_Steps_1034 {

  /**
   * This upgrader changes the labels for the default assignee options used in
   * case type management.
   *
   * @return bool
   */
  public function upgrade_1034() {
    $optionValuesNewLabels = [
      'BY_RELATIONSHIP' => 'By relationship to target staff member',
      'USER_CREATING_THE_CASE' => 'User who starts the workflow',
    ];

    $optionValuesNames = array_keys($optionValuesNewLabels);
    $optionValues = civicrm_api3('OptionValue', 'get', [
      'name' => ['IN' => $optionValuesNames],
    ]);

    foreach ($optionValues['values'] as $optionValue) {
      $newLabel = $optionValuesNewLabels[$optionValue['name']];

      civicrm_api3('OptionValue', 'create', [
        'id' => $optionValue['id'],
        'label' => $newLabel,
      ]);
    }

    return TRUE;
  }

}
