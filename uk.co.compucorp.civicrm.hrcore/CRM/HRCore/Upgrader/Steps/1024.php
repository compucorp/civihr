<?php

trait CRM_HRCore_Upgrader_Steps_1024 {

  /**
   * Changes the labels for some of the Case Type's default assignee options.
   *
   * @return bool
   */
  public function upgrade_1024() {
    $newLabels = [
      'BY_RELATIONSHIP' => 'By relationship to target staff member',
      'USER_CREATING_THE_CASE' => 'User who starts the workflow',
    ];

    $names = array_keys($newLabels);

    $optionValues = civicrm_api3('OptionValue', 'get', [
      'name' => ['IN' => $names],
      'option_group_id' => 'activity_default_assignee',
    ]);

    foreach ($optionValues['values'] as $optionValue) {
      $newLabel = $newLabels[$optionValue['name']];

      civicrm_api3('OptionValue', 'create', [
        'id' => $optionValue['id'],
        'label' => $newLabel,
      ]);
    }

    return TRUE;
  }

}
