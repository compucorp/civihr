<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData as LeaveRequestCalendarFeedData;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestCalendarFeedConfig as LeaveRequestCalendarFeedConfig;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcal as LeaveRequestCalendarFeedIcal;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcalTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcalTest extends BaseHeadlessTest {

  public function testGetReturnsAnIcalDataFormat() {
    $this->requireIcalLibrary();
    $date1 = new DateTime('today');
    $date2 = new DateTime('tomorrow');
    $sampleData = [
      [
        'id' => 1,
        'contact_id' => 3,
        'display_name' => 'John Holt (Vacation)',
        'from_date' => $date1->format('Y-m-d H:i:s'),
        'to_date' => $date2->format('Y-m-d H:i:s'),
      ],
      [
        'id' => 2,
        'contact_id' => 5,
        'display_name' => 'Nicholas Cage (Sick)',
        'from_date' => $date2->format('Y-m-d H:i:s'),
        'to_date' => $date2->format('Y-m-d H:i:s'),
      ],
    ];

    $feedConfig = $this->createLeaveCalendarFeedConfig([]);
    $leaveFeedData = $this->prophesize(LeaveRequestCalendarFeedData::class);
    $leaveFeedData->getFeedConfig()->willReturn($feedConfig);
    $leaveFeedData->getStartDate()->willReturn(new DateTime('today'));
    $leaveFeedData->getEndDate()->willReturn(new DateTime('+3 months'));
    $leaveFeedData->get()->willReturn($sampleData);

    $feedIcal = new LeaveRequestCalendarFeedIcal();
    $feedIcal = $feedIcal->get($leaveFeedData->reveal());

    $icalObject = new ZCiCal($feedIcal);
    //Two events are present.
    $this->assertEquals(2, $icalObject->countEvents());
    $eventDetails = $this->extractEventDetails($icalObject);
    $firstEvent = array_shift($eventDetails);
    $secondEvent = array_shift($eventDetails);

    $this->assertEquals($firstEvent['SUMMARY'], $sampleData[0]['display_name']);
    $this->assertEquals($firstEvent['UID'], $sampleData[0]['id'] . '_feed_' . $sampleData[0]['contact_id']);
    $this->assertEquals($firstEvent['DTSTART'], $date1->format('Ymd\THis'));
    $this->assertEquals($firstEvent['DTEND'], $date2->format('Ymd\THis'));

    $this->assertEquals($secondEvent['SUMMARY'], $sampleData[1]['display_name']);
    $this->assertEquals($secondEvent['UID'], $sampleData[1]['id'] . '_feed_' . $sampleData[1]['contact_id']);
    $this->assertEquals($secondEvent['DTSTART'], $date2->format('Ymd\THis'));
    $this->assertEquals($secondEvent['DTEND'], $date2->format('Ymd\THis'));
  }

  private function extractEventDetails($icalObject) {
    $events = [];
    if(isset($icalObject->tree->child)) {
      $counter = 0;
      foreach($icalObject->tree->child as $node) {
        if($node->getName() == "VEVENT") {
          foreach($node->data as $key => $value) {
            if(is_array($value)) {
              for($i = 0; $i < count($value); $i++) {
                $events[$counter][$key] = $value[$i]->getValues();
              }
            }
            else {
              $events[$counter][$key] = $value->getValues();
            }
          }
        }
        $counter++;
      }
    }

    return $events;
  }
  private function requireIcalLibrary() {
    $calendarLibraryPath =
      CRM_Core_Resources::singleton()->getPath('uk.co.compucorp.civicrm.hrcore') . '/vendor/icalendar/zapcallib.php';
    require_once("$calendarLibraryPath");
  }

  private function createLeaveCalendarFeedConfig($params) {
    $defaultParameters = [
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
      'composed_of' => [
        'leave_type' => [1]
      ],
      'visible_to' => []
    ];

    $params = array_merge($defaultParameters, $params);

    return LeaveRequestCalendarFeedConfig::create($params);
  }
}
