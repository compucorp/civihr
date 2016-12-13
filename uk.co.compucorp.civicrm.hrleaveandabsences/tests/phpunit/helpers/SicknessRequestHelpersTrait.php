<?php


trait CRM_HRLeaveAndAbsences_SicknessRequestHelpersTrait {

  protected $requiredDocumentOptions = [];

  protected function requiredDocumentOptionsBuilder() {

    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'hrleaveandabsences_leave_request_required_document',
    ]);
    $requiredDocumentOptions = [];

    foreach ($result['values'] as $requiredDocument) {
      $option = [
        'id' => $requiredDocument['id'],
        'value' => $requiredDocument['value'],
        'name' => $requiredDocument['name'],
        'label' => $requiredDocument['label']
      ];
      $requiredDocumentOptions[$requiredDocument['label']] = $option;
    }
    return $requiredDocumentOptions;
  }
}
