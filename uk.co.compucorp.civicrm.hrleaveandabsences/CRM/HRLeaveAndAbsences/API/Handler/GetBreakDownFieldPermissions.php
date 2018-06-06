<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;

class CRM_HRLeaveAndAbsences_API_Handler_GetBreakDownFieldPermissions extends CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissions {

  /**
   * @var int|string
   *   The contact ID whom the request results belongs to.
   */
  private $contactID;

  /**
   * {@inheritdoc}
   */
  protected function getDataRowIdentifierKey() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataRowIdentifierLevel() {
    return NULL;
  }

  /**
   * CRM_HRLeaveAndAbsences_API_Handler_GetBreakDownFieldPermissions constructor.
   *
   * @param array $apiRequest
   * @param LeaveRequestRightsService $leaveRequestRights
   */
  public function __construct(array $apiRequest, LeaveRequestRightsService $leaveRequestRights) {
    $this->contactID = $this->getContactID($apiRequest);

    parent::__construct($apiRequest, $leaveRequestRights);
  }

  /**
   * Gets the contact ID that the request results belongs to.
   *
   * @param array $apiRequest
   *
   * @return int|string
   */
  private function getContactID($apiRequest) {
    $leaveRequestID = $apiRequest['params']['leave_request_id'];

    try {
      $leaveRequest = LeaveRequest::findById($leaveRequestID);

      return $leaveRequest->contact_id;
    } catch(Exception $e) {
      return '';
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getRestrictedFields() {
    $fields = [
      'amount' => [
        'restricted_value' => '',
        'level' => 1
      ],
    ];

    return $fields;
  }

  /**
   * Checks whether current user has access to the restricted data for the contact_id linked
   * to the request results.
   *
   * @param mixed $dataRowIdentifierValue
   *
   * @return bool
   */
  protected function canAccessRestrictedData($dataRowIdentifierValue) {
    return in_array($this->contactID, $this->getAccessibleContacts());
  }
}
