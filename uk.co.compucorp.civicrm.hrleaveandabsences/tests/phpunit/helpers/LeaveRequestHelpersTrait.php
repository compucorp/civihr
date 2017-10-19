<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;

trait CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait {

  protected $leaveRequestDayTypes = [];
  protected $leaveRequestStatuses = [];

  protected function getLeaveRequestDayTypes() {
    if(empty($this->leaveRequestDayTypes)) {
      $leaveRequestDayTypeOptions = LeaveRequest::buildOptions('from_date_type');
      foreach($leaveRequestDayTypeOptions  as $key => $label) {
        $name = CRM_Core_Pseudoconstant::getName(LeaveRequest::class, 'from_date_type', $key);
        $this->leaveRequestDayTypes[$name] = [
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
    return [
      'approved' => 1,
      'admin_approved' => 2,
      'awaiting_approval' => 3,
      'more_information_required' => 4,
      'rejected' => 5,
      'cancelled' => 6,
    ];
  }

  public function openLeaveRequestStatusesDataProvider() {
    $leaveRequestStatuses = $this->getLeaveRequestStatuses();

    return [
      [$leaveRequestStatuses['more_information_required']],
      [$leaveRequestStatuses['awaiting_approval']],
    ];
  }

  public function closedLeaveRequestStatusesDataProvider() {
    $leaveRequestStatuses = $this->getLeaveRequestStatuses();

    return [
      [$leaveRequestStatuses['cancelled']],
      [$leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['admin_approved']],
      [$leaveRequestStatuses['approved']],
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

  public function createCommentForLeaveRequest($params) {
    $defaultParams = ['text' => 'Sample Text',];
    $payload = array_merge($defaultParams, $params);

    $leaveRequestCommentService = new LeaveRequestCommentService();
    return $leaveRequestCommentService->add($payload);
  }
}
