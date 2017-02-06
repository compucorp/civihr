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
   * Checks whether the current user can cancel a leave request or not
   * on behalf of the given contactID based on a set of rules.
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  public function canCancelFor($contactID) {
    return $this->currentUserIsManagerOrAdmin($contactID) || $this->currentUserIsLeaveContact($contactID);
  }

  /**
   * Checks whether the current user can approve a leave request or not
   * on behalf of the given contactID
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  public function canApproveFor($contactID) {
    return $this->currentUserIsManagerOrAdmin($contactID);
  }

  /**
   * Checks whether the current user can reject a leave request or not
   * on behalf of the given contactID
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  public function canRejectFor($contactID) {
    return $this->currentUserIsManagerOrAdmin($contactID);
  }

  /**
   * Checks whether the current user can request more information or not
   * for a leave request on behalf of the given contactID
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  public function canRequestMoreInformationFor($contactID) {
    return $this->currentUserIsManagerOrAdmin($contactID);
  }

  /**
   * Checks whether the current user can put a leave request in waiting approval
   * or not on behalf of the given contactID
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  public function canPutInWaitingForApprovalFor($contactID) {
    return $this->currentUserIsManagerOrAdmin($contactID);
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
   * @param $contactID
   *   The contactID of the leave request
   * @param $statusID
   *   The statusID of the leave request
   *
   * @return bool
   */
  public function canChangeDatesFor($contactID, $statusID) {
    $leaveRequestStatuses = self::getLeaveRequestStatuses();
    return $this->currentUserIsLeaveContact($contactID) &&
           in_array($statusID, [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['more_information_requested']]);
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
           in_array($statusID, [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['more_information_requested']]);
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
    return $this->leaveManagerService->currentUserIsLeaveManagerOf($contactID) ||
           $this->leaveManagerService->currentUserIsAdmin();
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
