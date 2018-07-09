<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * Class CRM_HRLeaveAndAbsences_Helper_CalendarFeed_LeaveTime
 */
class CRM_HRLeaveAndAbsences_Helper_CalendarFeed_LeaveTime {

  /**
   * A helper method to help adjust the leave feed data dates before it is converted to
   * Ical format.
   *
   * @param array $leaveRequest
   */
  public static function adjust(&$leaveRequest) {
    if (empty($leaveRequest['from_date_type']) && empty($leaveRequest['to_date_type'])) {
      return;
    }

    $leaveDayTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    $fromDate = new DateTime($leaveRequest['from_date']);
    $toDate = new DateTime($leaveRequest['to_date']);
    $isSameDayRequest = $fromDate->format('Y-m-d') === $toDate->format('Y-m-d');

    if ($isSameDayRequest && $leaveRequest['from_date_type'] == $leaveDayTypes['half_day_am']) {
      $fromDate->setTime('06', '00');
      $toDate->setTime('12', '00');
    }

    if ($isSameDayRequest && $leaveRequest['from_date_type'] == $leaveDayTypes['half_day_pm']) {
      $fromDate->setTime('12', '01');
      $toDate->setTime('19', '00');
    }

    if ($isSameDayRequest && $leaveRequest['from_date_type'] == $leaveDayTypes['all_day']) {
      $fromDate->setTime('06', '00');
      $toDate->setTime('19', '00');
    }

    if (!$isSameDayRequest &&
      in_array($leaveRequest['from_date_type'], [$leaveDayTypes['all_day'], $leaveDayTypes['half_day_am']])) {
      $fromDate->setTime('06', '00');
    }

    if (!$isSameDayRequest && $leaveRequest['from_date_type'] == $leaveDayTypes['half_day_pm']) {
      $fromDate->setTime('12', '01');
    }

    if (!$isSameDayRequest && $leaveRequest['to_date_type'] == $leaveDayTypes['half_day_am']) {
      $toDate->setTime('12', '00');
    }

    if (!$isSameDayRequest &&
      in_array($leaveRequest['to_date_type'], [$leaveDayTypes['all_day'], $leaveDayTypes['half_day_pm']])) {
      $toDate->setTime('19', '00');
    }

    $leaveRequest['from_date'] = $fromDate->format('Y-m-d H:i:s');
    $leaveRequest['to_date'] = $toDate->format('Y-m-d H:i:s');
  }
}
