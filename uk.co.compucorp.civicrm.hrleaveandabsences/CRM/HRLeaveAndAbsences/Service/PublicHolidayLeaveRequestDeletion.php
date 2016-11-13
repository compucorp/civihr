<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion {

  /**
   * Deletes the Public Holiday Leave Request for the contact and Public Holiday
   *
   * @param int $contactID
   * @param \CRM_HRLeaveAndAbsences_BAO_PublicHoliday $publicHoliday
   */
  public function deleteForContact($contactID, PublicHoliday $publicHoliday) {
    $leaveRequest = LeaveRequest::findPublicHolidayLeaveRequest($contactID, $publicHoliday);

    if(!$leaveRequest) {
      return;
    }

    foreach($leaveRequest->getDates() as $date) {
      LeaveBalanceChange::deleteForLeaveRequestDate($date);
      $date->delete();
    }

    $leaveRequest->delete();
  }

}
