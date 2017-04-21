<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait {

  protected $leaveRequestDayTypes = [];
  protected $leaveRequestStatuses = [];

  protected function getLeaveRequestDayTypes() {
    if(empty($this->leaveRequestDayTypes)) {
      $leaveRequestDayTypeOptions = LeaveRequest::buildOptions('from_date_type');
      foreach($leaveRequestDayTypeOptions  as $key => $label) {
        $name = CRM_Core_Pseudoconstant::getName(LeaveRequest::class, 'from_date_type', $key);
        $this->leaveRequestDayTypes[$label] = [
          'id' => $key,
          'value' => $key,
          'name' => $name,
          'label' => $label
        ];
      }
    }

    return $this->leaveRequestDayTypes;
  }

  protected function getLeaveRequestStatuses() {
    if(empty($this->leaveRequestStatuses)) {
      $leaveRequestStatusOptions = LeaveRequest::buildOptions('status_id');
      foreach($leaveRequestStatusOptions  as $key => $label) {
        $name = CRM_Core_Pseudoconstant::getName(LeaveRequest::class, 'status_id', $key);
        $this->leaveRequestStatuses[$label] = [
          'id' => $key,
          'value' => $key,
          'name' => $name,
          'label' => $label
        ];
      }
    }

    return $this->leaveRequestStatuses;
  }

  public function openLeaveRequestStatusesDataProvider() {
    $leaveRequestStatuses = $this->getLeaveRequestStatuses();

    return [
      [$leaveRequestStatuses['More Information Requested']['id']],
      [$leaveRequestStatuses['Waiting Approval']['id']],
    ];
  }

  public function closedLeaveRequestStatusesDataProvider() {
    $leaveRequestStatuses = $this->getLeaveRequestStatuses();

    return [
      [$leaveRequestStatuses['Cancelled']['id']],
      [$leaveRequestStatuses['Rejected']['id']],
      [$leaveRequestStatuses['Admin Approved']['id']],
      [$leaveRequestStatuses['Approved']['id']],
    ];
  }

  protected function createAttachmentForLeaveRequest($params) {
    $defaultParams = [
      'entity_table' => LeaveRequest::getTableName(),
      'name' => 'LeaveRequestSampleFile.txt',
      'mime_type' => 'text/plain',
      'content' => '',
      'sequential' => 1,
    ];
    $payload = array_merge($defaultParams, $params);
    $result =  civicrm_api3('Attachment', 'create', $payload);

    return $result['values'][0];
  }

  protected function getAttachmentForLeaveRequest($params) {
    $defaultParams = [
      'entity_table' => LeaveRequest::getTableName(),
      'sequential' => 1
    ];
    $payload = array_merge($defaultParams, $params);
    $result =  civicrm_api3('Attachment', 'get', $payload);

    return $result;
  }

  protected function getSicknessRequiredDocuments() {
    $result = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'hrleaveandabsences_leave_request_required_document',
    ]);

    $options = [];
    foreach ($result['values'] as $requiredDocument) {
      $options[$requiredDocument['value']] = $requiredDocument['label'];
    }

    return $options;
  }
}
