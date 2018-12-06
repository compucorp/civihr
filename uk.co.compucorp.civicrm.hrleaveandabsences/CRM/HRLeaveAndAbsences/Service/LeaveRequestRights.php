<?php

use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

class CRM_HRLeaveAndAbsences_Service_LeaveRequestRights {

  use CRM_HRLeaveAndAbsences_ACL_LeaveInformationTrait;

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
    $currentUserIsLeaveContact = $this->currentUserIsLeaveContact($contactID);
    $isAdmin = $this->currentUserIsAdmin();

    if ($isAdmin && !$currentUserIsLeaveContact) {
      return TRUE;
    }

    $isOpenLeaveRequest = in_array($statusID, LeaveRequest::getOpenStatuses());

    if ($currentUserIsLeaveContact && $isOpenLeaveRequest) {
      return TRUE;
    }

    $currentUserIsLeaveManager = $this->currentUserIsLeaveManagerOf($contactID);
    $isOwnLeaveApprover = $currentUserIsLeaveManager && $currentUserIsLeaveContact;
    $isSicknessRequest = $requestType === LeaveRequest::REQUEST_TYPE_SICKNESS;

    return $isOwnLeaveApprover || ($isSicknessRequest && $currentUserIsLeaveManager);
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
    $isOpenLeaveRequest = in_array($statusID, LeaveRequest::getOpenStatuses());
    $isLeaveContactOrAdmin = $this->currentUserIsLeaveContact($contactID) || $this->currentUserIsAdmin();

    return $isLeaveContactOrAdmin && $isOpenLeaveRequest;
  }

  /**
   * Checks whether the current user has permissions to delete the leave request.
   * Currently only allows the admin and a user who is own leave approver and its
   * own request to delete a leave request.
   * For cases where the admin is the leave request contact and not own leave
   * approver, the admin is not allowed to delete the request.
   *
   * @param int $contactID
   *   The contactID of the leave request
   *
   * @return bool
   */
  public function canDeleteFor($contactID) {
    $isLeaveContact = $this->currentUserIsLeaveContact($contactID);
    $isAdmin = $this->currentUserIsAdmin();

    if ($isAdmin && !$isLeaveContact) {
      return TRUE;
    }

    $isLeaveManager = $this->currentUserIsLeaveManagerOf($contactID);
    $isOwnLeaveApprover = $isLeaveContact && $isLeaveManager;

    if ($isOwnLeaveApprover) {
      return TRUE;
    }

    return FALSE;
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
   * Checks whether the current user can cancel the TOIL Request with
   * past dates or not.
   *
   * @param int $contactID
   *   The contactID of the leave request
   * @param int $absenceTypeID
   *   The absence Type ID of the leave request
   *
   * @return bool
   */
  public function canCancelToilWithPastDates($contactID, $absenceTypeID) {
    $absenceType = AbsenceType::findById($absenceTypeID);

    if(!$absenceType->allow_accrue_in_the_past) {
      return $this->currentUserIsManagerOrAdmin($contactID);
    }

    return TRUE;
  }

  /**
   * Returns the leave contact ids that the current logged in user has access to, For a
   * user with staff role it would be that user contact id alone, for a manager it
   * would be the contact Id's of the staff he approves leave for. The query is not ran
   * for an Admin user because in reality an Admin user has access to all contacts.
   *
   * @return array
   */
  public function getLeaveContactsCurrentUserHasAccessTo() {
    $results = [];

    if($this->currentUserIsAdmin()) {
      return $results;
    }
    $query = $this->getLeaveInformationACLQuery();
    $contactIds = CRM_CORE_DAO::executeQuery($query);

    while ($contactIds->fetch()) {
      $results[] = $contactIds->id;
    }

    return $results;
  }

  /**
   * Checks if the current user can cancel the leave request for the given absence type,
   * leave contact and leave from date.
   *
   * @param int $absenceTypeId
   * @param int $contactId
   * @param \DateTime $leaveFromDate
   *
   * @return bool
   */
  public function canCancelForAbsenceType($absenceTypeId, $contactId, DateTime $leaveFromDate) {
    $isLeaveContact = $this->currentUserIsLeaveContact($contactId);
    $isAdmin = $this->currentUserIsAdmin();

    if (($isAdmin && !$isLeaveContact) || $this->currentUserIsLeaveManagerOf($contactId)) {
      return TRUE;
    }

    $absenceType = AbsenceType::findById($absenceTypeId);
    if ($absenceType->allow_request_cancelation == AbsenceType::REQUEST_CANCELATION_ALWAYS) {
      return TRUE;
    }

    $today = new DateTime('today');

    $absenceTypeAllowsFutureCancellation =
      $absenceType->allow_request_cancelation == AbsenceType::REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE;
    if ($absenceTypeAllowsFutureCancellation && $leaveFromDate > $today) {
      return TRUE;
    }

    return FALSE;
  }
}
