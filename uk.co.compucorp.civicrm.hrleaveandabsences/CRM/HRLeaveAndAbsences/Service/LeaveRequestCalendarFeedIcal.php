<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData as LeaveRequestCalendarFeedData;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcal
 *
 * This class converts the leave feed data to an ical format. It
 * accepts a LeaveRequestCalendarFeedData object to achieve this.
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcal {

  /**
   * Returns the Leave data formatted in Ical format.
   *
   * @param LeaveRequestCalendarFeedData $feedData
   *
   * @return string
   *   Leave data formatted in Ical format
   */
  public function get(LeaveRequestCalendarFeedData $feedData) {
    $this->requireIcalLibrary();

    $icalObject = new ZCiCal();
    ZCTimeZoneHelper::getTZNode(
      $feedData->getStartDate()->format('Y'),
      $feedData->getEndDate()->format('Y'),
      $feedData->getTimeZone(),
      $icalObject->curnode
    );

    foreach ($feedData->get() as $data) {
      $eventObject = new ZCiCalNode("VEVENT", $icalObject->curnode);
      $eventObject->addNode(new ZCiCalDataNode("SUMMARY:" . $data['display_name']));
      $eventObject->addNode(new ZCiCalDataNode("DTSTART:" . ZCiCal::fromSqlDateTime($data['from_date'])));
      $eventObject->addNode(new ZCiCalDataNode("DTEND:" . ZCiCal::fromSqlDateTime($data['to_date'])));

      // The leave request is used as a string for this event so that on reimport by calendar app
      // the event is not duplicated and changes will be updated.
      $eventObject->addNode(new ZCiCalDataNode("UID:" . $data['id']));
      $eventObject->addNode(new ZCiCalDataNode("DTSTAMP:" . ZCiCal::fromSqlDateTime()));
    }

    return $icalObject->export();
  }

  /**
   * Includes the Ical library file.
   */
  private function requireIcalLibrary() {
    $calendarLibraryPath =
      CRM_Core_Resources::singleton()->getPath('uk.co.compucorp.civicrm.hrcore') . '/vendor/icalendar/zapcallib.php';
    require_once("$calendarLibraryPath");
  }
}
