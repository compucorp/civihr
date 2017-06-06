<?php

trait CRM_HRLeaveAndAbsences_WorkPatternHelpersTrait {

  public function getWorkDayTypeOptionsArray() {
    $result = $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'option_group_id' => 'hrleaveandabsences_work_day_type',
    ]);

    $options = [];
    foreach($result['values'] as $value) {
      $options[$value['name']] = [
        'value' => $value['value'],
        'name' => $value['name'],
        'label' => $value['label']
      ];
    }

    return $options;
  }

}
