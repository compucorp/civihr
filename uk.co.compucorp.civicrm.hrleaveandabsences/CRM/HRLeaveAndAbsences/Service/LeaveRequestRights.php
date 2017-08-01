<?php

use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

class CRM_HRLeaveAndAbsences_Service_LeaveRequestRights {

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveManager
   */
  private $leaveManagerService;

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest status_id field.
   */
  private static $leaveStatuses;

  /**
   * CRM_HRLeaveAndAbsences_Service_LeaveRequestRights constructor.
   *
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveManager $leaveManagerService
   */
  public function __construct(LeaveManagerService $leaveManagerService) {
    $this->leaveManagerService = $leaveManagerService;
  }

  /**
   * Checks whether the current user has permissions to create/update the leave request
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  public function canCreateAndUpdateFor($contactID) {
    return $this->currentUserIsLeaveContact($contactID) || $this->currentUserIsManagerOrAdmin($contactID);
  }

  /**
   * Checks whether the current user can update the dates for the leave request or not.
   *
   * @param int $contactID
   *   The contactID of the leave request
   * @param int $statusID
   *   The statusID of the leave request
   * @param string $requestType
   *   The type of the leave request (toil, sickness, leave)
   *
   * @return bool
   */
  public function canChangeDatesFor($contactID, $statusID, $requestType) {
    $leaveRequestStatuses = self::getLeaveRequestStatuses();
    $openStatuses = [
      $leaveRequestStatuses['awaiting_approval'],
      $leaveRequestStatuses['more_information_required']
    ];
    $isSicknessRequest = $requestType === LeaveRequest::REQUEST_TYPE_SICKNESS;
    $isOpenLeaveRequest = in_array($statusID, $openStatuses);

    $currentUserCanChangeDates = ($isSicknessRequest && $this->currentUserIsLeaveManagerOf($contactID)) ||
                                 ($this->currentUserIsLeaveContact($contactID) && $isOpenLeaveRequest) ||
                                  $this->currentUserIsAdmin();

    return $currentUserCanChangeDates;
  }

  /**
   * Checks whether the current user can update the absence type for the leave request or not.
   *
   * @param int $contactID
   *   The contactID of the leave request
   * @param int $statusID
   *   The statusID of the leave request
   *
   * @return bool
   */
  public function canChangeAbsenceTypeFor($contactID, $statusID) {
    $leaveRequestStatuses = self::getLeaveRequestStatuses();
    return $this->currentUserIsLeaveContact($contactID) &&
           in_array($statusID, [$leaveRequestStatuses['awaiting_approval'], $leaveRequestStatuses['more_information_required']]);
  }

  /**
   * Checks whether the current user has permissions to delete the leave request
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  public function canDeleteFor($contactID) {
    return $this->currentUserIsAdmin();
  }

  /**
   * Checks if the current user is an Admin
   *
   * @return bool
   */
  private function currentUserIsAdmin() {
    return $this->leaveManagerService->currentUserIsAdmin();
  }

  /**
   * Checks if the current user is a leave manager of the contact ID passed in
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  private function currentUserIsLeaveManagerOf($contactID) {
    return $this->leaveManagerService->currentUserIsLeaveManagerOf($contactID);
  }

  /**
   * Checks if the current user is either a leave manager or an Admin
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  private function currentUserIsManagerOrAdmin($contactID) {
    return $this->currentUserIsLeaveManagerOf($contactID) ||
           $this->currentUserIsAdmin();
  }

  /**
   * Checks whether the current user is the leave request contact or not.
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  private function currentUserIsLeaveContact($contactID) {
    return CRM_Core_Session::getLoggedInContactID() == $contactID;
  }

  /**
   * Returns the array of the option values for the LeaveRequest status_id field.
   *
   * @return array
   */
  private static function getLeaveRequestStatuses() {
    if (is_null(self::$leaveStatuses)) {
      self::$leaveStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));
    }

    return self::$leaveStatuses;
  }
}
