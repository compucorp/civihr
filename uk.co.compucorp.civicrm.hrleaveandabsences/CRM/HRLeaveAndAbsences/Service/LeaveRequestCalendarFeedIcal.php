<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData as LeaveRequestCalendarFeedData;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcal
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

    $feedConfig = $feedData->getFeedConfig();

    $icalObject = new ZCiCal();
    ZCTimeZoneHelper::getTZNode(
      $feedData->getStartDate()->format('Y'),
      $feedData->getEndDate()->format('Y'),
      $feedConfig->timezone,
      $icalObject->curnode
    );

    foreach ($feedData->get() as $data) {
      $eventObject = new ZCiCalNode("VEVENT", $icalObject->curnode);
      $eventObject->addNode(new ZCiCalDataNode("SUMMARY:" . $data['display_name']));
      $eventObject->addNode(new ZCiCalDataNode("DTSTART:" . ZCiCal::fromSqlDateTime($data['from_date'])));
      $eventObject->addNode(new ZCiCalDataNode("DTEND:" . ZCiCal::fromSqlDateTime($data['to_date'])));

      // We need to create a unique string for this event so that on reimport by calendar app
      // Event is not duplicated and changes will be updated.
      $uid = $data['id'] . '_feed_' . $data['contact_id'];
      $eventObject->addNode(new ZCiCalDataNode("UID:" . $uid));
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
