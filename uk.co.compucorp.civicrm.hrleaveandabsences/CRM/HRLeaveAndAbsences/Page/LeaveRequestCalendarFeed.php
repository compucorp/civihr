<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData as LeaveRequestCalendarFeedData;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcal as LeaveRequestCalendarFeedIcal;

/**
 * Class CRM_HRLeaveAndAbsences_Page_LeaveRequestCalendarFeed
 */
class CRM_HRLeaveAndAbsences_Page_LeaveRequestCalendarFeed {

  /**
   * Returns the Leave data ical file as an attachment or echos the
   * error message in case an exception is thrown.
   */
  public static function get() {
    try {
      $feedHash = CRM_Utils_Array::value('hash', $_GET);
      $leaveFeedData = new LeaveRequestCalendarFeedData($feedHash);
      $leaveFeedIcal = new LeaveRequestCalendarFeedIcal();
      $leaveFeedIcal = $leaveFeedIcal->get($leaveFeedData);

      CRM_Utils_System::download('cal.ics', 'text/calendar', $leaveFeedIcal);
      CRM_Utils_System::civiExit();

    } catch(Exception $e) {
      http_response_code(404);
      CRM_Utils_System::civiExit();
    }
  }
}
