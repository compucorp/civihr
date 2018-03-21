<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

trait CRM_HRLeaveAndAbsences_Upgrader_Step_1020 {

  /**
   * Sets "end" time for all existing TOIL (overtime) requests to 23:45.
   *
   * As per PCHR-3427 TOIL (overtime) requests have both "from" and "to" times now.
   * Currently, TOIL requests in days have end time set as 23:59, TOIL requests in hours
   * have end time set as 00:00. There are two problems that this upgrader solves:
   *
   * Problem 1. The interval for TOIL time as per PCHR-3427 is now 15 minutes, which means
   * 23:59 is not a valid time anymore and must be amended.
   *
   * Problem 2. The timeframe of 00:00 - 00:00 does not make sense. If it is a
   * single day TOIL, then the date will be the same, meaning the request timeframe
   * duration equals to 0. If a request covers, for example, 2 days, then the
   * difference between "end" and "start" date/times will be exactly 24 hours,
   * which also does not make sense.
   *
   * @return boolean
   */

  public function upgrade_1020 () {
    $leaveRequest = new LeaveRequest();

    $leaveRequest->whereAdd('request_type = "toil"');
    $leaveRequest->whereAdd('is_deleted = 0');
    $leaveRequest->find();

    while ($leaveRequest->fetch()) {
      $leaveRequest->to_date = substr($leaveRequest->to_date, 0, 11) . '23:45:00';

      $leaveRequest->update();
    }

    return true;
  }
}
