<?php

use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

class CRM_HRLeaveAndAbsences_Service_LeaveRequestRights {

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveManager
   */
  private $leaveManagerService;

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
    return $this->currentUserIsManagerOrAdmin($contactID) || CRM_Core_Session::getLoggedInContactID() == $contactID;
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
}
