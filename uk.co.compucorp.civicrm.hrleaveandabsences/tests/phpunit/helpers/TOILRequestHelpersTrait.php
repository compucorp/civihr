<?php

trait CRM_HRLeaveAndAbsences_TOILRequestHelpersTrait {

  protected $toilAmounts = [];

  protected function toilAmountOptions() {

    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'hrleaveandabsences_toil_amounts',
    ]);
    $toilAmounts = [];

    foreach ($result['values'] as $toilAmount) {
      $option = [
        'id' => $toilAmount['id'],
        'value' => $toilAmount['value'],
        'name' => $toilAmount['name'],
        'label' => $toilAmount['label']
      ];
      $toilAmounts[$toilAmount['label']] = $option;
    }
    return $toilAmounts;
  }
}
