<?php


trait CRM_HRLeaveAndAbsences_SicknessRequestHelpersTrait {

  protected $requiredDocumentOptions = [];
  protected $sicknessRequestReasons = [];

  protected function getSicknessRequestRequiredDocumentsOptions() {
    if(empty($this->requiredDocumentOptions)) {
      $result = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => 'hrleaveandabsences_leave_request_required_document',
      ]);

      foreach ($result['values'] as $requiredDocument) {
        $option = [
          'id' => $requiredDocument['id'],
          'value' => $requiredDocument['value'],
          'name' => $requiredDocument['name'],
          'label' => $requiredDocument['label']
        ];

        $this->requiredDocumentOptions[$requiredDocument['label']] = $option;
      }
    }

    return $this->requiredDocumentOptions;
  }

  protected function getSicknessRequestReasons() {
    if(empty($this->sicknessRequestReasons)) {
      $result = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => 'hrleaveandabsences_sickness_reason',
      ]);

      foreach ($result['values'] as $requiredDocument) {
        $option = [
          'id' => $requiredDocument['id'],
          'value' => $requiredDocument['value'],
          'name' => $requiredDocument['name'],
          'label' => $requiredDocument['label']
        ];

        $this->sicknessRequestReasons[$requiredDocument['label']] = $option;
      }
    }

    return $this->sicknessRequestReasons;
  }
}
