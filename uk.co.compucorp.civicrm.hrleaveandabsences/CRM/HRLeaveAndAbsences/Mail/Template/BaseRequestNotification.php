<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

abstract class CRM_HRLeaveAndAbsences_Mail_Template_BaseRequestNotification {

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
   * Gets the message template ID for this Template Type.
   *
   * @return int
   */
  abstract public function getTemplateID();

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
      'fromDate' => $leaveRequest->from_date,
      'toDate' => $leaveRequest->to_date,
      'fromDateType' => $this->getLeaveRequestDayTypeLabel($leaveRequest->from_date_type),
      'toDateType' => $this->getLeaveRequestDayTypeLabel($leaveRequest->to_date_type),
      'leaveStatus' => $this->getLeaveRequestStatusLabel($leaveRequest->status_id),
      'leaveRequest' => $leaveRequest,
      'absenceTypeName' => $this->getAbsenceTypeName($leaveRequest),
      'currentDateTime' => new DateTime(),
      'calculationUnitName' => $this->getAbsenceTypeCalculationUnitName($leaveRequest)
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
   * Gets the Name of the Absence Type for this LeaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return string
   */
  private function getAbsenceTypeName(LeaveRequest $leaveRequest) {
    $absenceType = new CRM_HRLeaveAndAbsences_BAO_AbsenceType();
    $absenceType->id = $leaveRequest->type_id;
    $absenceType->find(true);

    return $absenceType->title;
  }

  /**
   * Gets the Name of the Absence Type Calculation Unit for a LeaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return string
   */
  private function getAbsenceTypeCalculationUnitName(LeaveRequest $leaveRequest) {
    $absenceType = new CRM_HRLeaveAndAbsences_BAO_AbsenceType();
    $absenceType->id = $leaveRequest->type_id;
    $absenceType->find(true);
    $calculationUnitId = $absenceType->calculation_unit;
    $calculationUnitOptions = AbsenceType::buildOptions('calculation_unit', 'validate');

    return $calculationUnitOptions[$calculationUnitId];
  }
}
