<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;

abstract class CRM_HRLeaveAndAbsences_Mail_Template_BaseRequestNotificationTemplate {

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveRequestComment
   */
  private $leaveRequestCommentService;

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest status_id field.
   */
  private $leaveStatuses;

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest from_date_type field.
   */
  private $leaveRequestDayTypes;

  /**
   * CRM_HRLeaveAndAbsences_Mail_BaseRequestNotificationTemplate constructor.
   *
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveRequestComment $leaveRequestComment
   */
  public function __construct(LeaveRequestCommentService $leaveRequestComment) {
    $this->leaveRequestCommentService = $leaveRequestComment;
  }

  /**
   * Gets the message template for this Template Type.
   *
   * @return array
   *  An array containing the message template attributes/values
   */
  abstract public function getTemplate();

  /**
   * Return parameters to be used in the Email smarty template
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   */
  public function getTemplateParameters(LeaveRequest $leaveRequest) {
    $templateParameters =  [
      'leaveComments' => $this->getLeaveComments($leaveRequest),
      'leaveFiles' => $this->getAttachments($leaveRequest),
      'fromDate' => $this->formatLeaveRequestDate($leaveRequest->from_date),
      'toDate' => $this->formatLeaveRequestDate($leaveRequest->to_date),
      'fromDateType' => $this->getLeaveRequestDayTypeLabel($leaveRequest->from_date_type),
      'toDateType' => $this->getLeaveRequestDayTypeLabel($leaveRequest->to_date_type),
      'leaveStatus' => $this->getLeaveRequestStatusLabel($leaveRequest->status_id),
      'leaveRequestLink' => $this->getLeaveRequestURL(),
      'leaveRequest' => $leaveRequest,
    ];

    return $templateParameters;
  }

  /**
   * Returns the label for the LeaveRequest status_id value
   *
   * @param int $statusValue
   *
   * @return array
   */
  private function getLeaveRequestStatusLabel($statusValue) {
    if (is_null($this->leaveStatuses)) {
      $this->leaveStatuses = LeaveRequest::buildOptions('status_id');
    }

    return $this->leaveStatuses[$statusValue];
  }

  /**
   * Returns the label for the LeaveRequest date type value.
   *
   * @param int $dayTypeValue
   *
   * @return array
   */
  private function getLeaveRequestDayTypeLabel($dayTypeValue) {
    if (is_null($this->leaveRequestDayTypes)) {
      $this->leaveRequestDayTypes = LeaveRequest::buildOptions('from_date_type');
    }

    return $this->leaveRequestDayTypes[$dayTypeValue];
  }

  /**
   * Gets the Comments associated with this LeaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   */
  private function getLeaveComments(LeaveRequest $leaveRequest) {
    $result = $this->leaveRequestCommentService->get([
      'leave_request_id' => $leaveRequest->id,
      'api.Contact.get' => ['id' => '$value.contact_id', 'return' => ['display_name']]
    ]);

    array_walk($result['values'], function(&$item){
      $item['commenter'] = $item['api.Contact.get']['values'][0]['display_name'];
    });

    return $result['values'];
  }

  /**
   * Gets the Attachments associated with this LeaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   */
  private function getAttachments(LeaveRequest $leaveRequest) {
    $result = civicrm_api3('LeaveRequest', 'getAttachments', [
      'leave_request_id' => $leaveRequest->id
    ]);

    return $result['values'];
  }

  /**
   * Return URL for the leave request on SSP
   *
   * @TODO This should return the actual url for the leave request in future.
   *
   * @return string
   */
  private function getLeaveRequestURL() {
    return CRM_Utils_System::url('my-leave', [], true);
  }

  /**
   * Format Leave Request date in 'Y-m-d' format
   *
   * @param string $date
   *
   * @return string
   */
  private function formatLeaveRequestDate($date) {
    $leaveDate = new DateTime($date);
    return $leaveDate->format('Y-m-d');
  }
}
