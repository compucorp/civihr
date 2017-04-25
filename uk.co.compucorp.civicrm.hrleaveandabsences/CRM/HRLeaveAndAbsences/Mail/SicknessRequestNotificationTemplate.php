<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

class CRM_HRLeaveAndAbsences_Mail_SicknessRequestNotificationTemplate extends
  CRM_HRLeaveAndAbsences_Mail_BaseRequestNotificationTemplate  {

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest sickness_required_documents field.
   */
  private $sicknessRequiredDocuments;

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest sickness_reason field.
   */
  private $sicknessReasons;

  /**
   * {@inheritdoc}
   */
  public function getTemplate() {
    $result = civicrm_api3('MessageTemplate', 'get', [
      'msg_title' => 'CiviHR Sickness Record Notification',
      'is_default' => 1,
      'sequential' => 1
    ]);

    return isset($result['values'][0]) ? $result['values'][0] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateParameters(LeaveRequest $leaveRequest) {
    $templateParameters = [];
    $templateParameters['sicknessReasons'] = $this->getSicknessReasons();
    if ($leaveRequest->sickness_required_documents) {
      $templateParameters['sicknessRequiredDocuments'] = $this->getSicknessRequiredDocuments();
      $templateParameters['leaveRequiredDocuments'] = explode(',', $leaveRequest->sickness_required_documents);
    }
    $templateParameters = array_merge(parent::getTemplateParameters($leaveRequest), $templateParameters);

    return $templateParameters;
  }

  /**
   * Returns the array of the option values for the LeaveRequest sickness_reason field.
   *
   * @return array
   */
  private function getSicknessReasons() {
    if (is_null($this->sicknessReasons)) {
      $this->sicknessReasons = LeaveRequest::buildOptions('sickness_reason');
    }

    return $this->sicknessReasons;
  }

  /**
   * Returns the array of the option values for the LeaveRequest sickness_required_documents field.
   *
   * @return array
   */
  private function getSicknessRequiredDocuments() {
    if (is_null($this->sicknessRequiredDocuments)) {
      $result = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => 'hrleaveandabsences_leave_request_required_document',
      ]);

      $options = [];
      foreach ($result['values'] as $requiredDocument) {
        $options[$requiredDocument['value']] = $requiredDocument['label'];
      }

      $this->sicknessRequiredDocuments = $options;
    }

    return $this->sicknessRequiredDocuments;
  }
}
