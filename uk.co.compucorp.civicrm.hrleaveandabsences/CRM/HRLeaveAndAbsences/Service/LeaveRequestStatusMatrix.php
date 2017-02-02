<?php

use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

class CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix {

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
   * CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix constructor.
   *
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveManager $leaveManagerService
   */
  public function __construct(LeaveManagerService $leaveManagerService) {
    $this->leaveManagerService = $leaveManagerService;
  }

  /**
   * Checks whether it is possible for the current user to change a leave request
   * status from fromStatus to toStatus given the leave request contact id
   * using the status matrix applicable to the current user
   *
   * @param int $fromStatus
   * @param int $toStatus
   * @param int $leaveRequestContactID
   *
   * @return bool
   */
  public function canTransitionTo($fromStatus, $toStatus, $leaveRequestContactID) {
    $statusMatrix = $this->getStatusMatrixForCurrentUser($leaveRequestContactID);

    if (empty($statusMatrix)) {
      return false;
    }

    return !empty($statusMatrix[$fromStatus]) && in_array($toStatus, $statusMatrix[$fromStatus]);
  }

  /**
   * Whether to use the manager status matrix for the current user or not
   *
   * @param int $leaveRequestContactID
   *
   * @return bool
   */
  private function shouldUseManagerMatrixForCurrentUser($leaveRequestContactID) {
    return $this->leaveManagerService->currentUserIsLeaveManagerOf($leaveRequestContactID) ||
           $this->leaveManagerService->currentUserIsAdmin();
  }

  /**
   * Returns an array with all the possible transitions for a staff member.
   *
   * Each item of the array is a single status (the item key) with a list of the next
   * statuses it can transition to. So, a item like:
   *
   *  1 => [3, 5, 6]
   *
   * Means that the status 1 can only be transitioned to the statuses 3, 5 and 6
   *
   * The null key represents the transition for a leave request with no prior status or an empty status
   *
   * @return array
   */
  private function getStaffStatusMatrix() {
    $leaveRequestStatuses = self::getLeaveRequestStatuses();
    $matrix = [];

    $matrix[$leaveRequestStatuses['waiting_approval']] = [
      $leaveRequestStatuses['waiting_approval'],
      $leaveRequestStatuses['cancelled']
    ];

    $matrix[$leaveRequestStatuses['more_information_requested']] = $matrix[$leaveRequestStatuses['waiting_approval']];

    $matrix[$leaveRequestStatuses['approved']] = [$leaveRequestStatuses['cancelled']];

    $matrix[NULL] = [$leaveRequestStatuses['waiting_approval']];

    return $matrix;
  }

  /**
   * Returns an array with all the possible transitions for a Leave approver or L&A Admin
   * The return array format is similar to getStaffStatusMatrix()
   *
   * The null key represents the transition for a leave request with no prior status or empty status
   *
   * @return array
   */
  private function getManagerStatusMatrix() {
    $leaveRequestStatuses = self::getLeaveRequestStatuses();
    $matrix = [];

    $matrix[$leaveRequestStatuses['waiting_approval']] = [
      $leaveRequestStatuses['more_information_requested'],
      $leaveRequestStatuses['rejected'],
      $leaveRequestStatuses['approved'],
      $leaveRequestStatuses['cancelled']
    ];

    $matrix[$leaveRequestStatuses['more_information_requested']] = $matrix[$leaveRequestStatuses['waiting_approval']];

    $matrix[$leaveRequestStatuses['rejected']] = $matrix[$leaveRequestStatuses['waiting_approval']];

    $matrix[$leaveRequestStatuses['approved']] = $matrix[$leaveRequestStatuses['waiting_approval']];

    $matrix[$leaveRequestStatuses['cancelled']] = array_merge(
      $matrix[$leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['waiting_approval']]
    );

    $matrix[NULL] = [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['approved']];

    return $matrix;
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

  /**
   * Method to get the right status matrix for the current user
   *
   * @param int $leaveRequestContactID
   *
   * @return array
   */
  private function getStatusMatrixForCurrentUser($leaveRequestContactID) {
    $currentUserID = CRM_Core_Session::getLoggedInContactID();
    $statusMatrix = [];

    if ($currentUserID == $leaveRequestContactID) {
      $statusMatrix = $this->getStaffStatusMatrix();
    }

    if ($this->shouldUseManagerMatrixForCurrentUser($leaveRequestContactID)) {
      $statusMatrix = $this->getManagerStatusMatrix();
    }

    return $statusMatrix;
  }
}
