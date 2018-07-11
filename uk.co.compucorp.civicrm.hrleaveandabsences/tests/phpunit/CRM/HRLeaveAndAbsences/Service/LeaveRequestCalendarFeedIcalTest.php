<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedData as LeaveRequestCalendarFeedData;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcal as LeaveRequestCalendarFeedIcal;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequestCalendarFeedConfig as LeaveCalendarFeedConfigFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcalTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestCalendarFeedIcalTest extends BaseHeadlessTest {

  public function testGetReturnsAnIcalDataFormat() {
    $dateTime = new DateTime();
    $date1 = new DateTime('2018-06-25');
    $date2 = new DateTime('2018-07-16');
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

    $feedConfig = LeaveCalendarFeedConfigFabricator::fabricate(['timezone' => 'America/Monterrey']);
    $leaveFeedData = $this->prophesize(LeaveRequestCalendarFeedData::class);
    $leaveFeedData->getInstantiatedDateTime()->willReturn($dateTime);
    $leaveFeedData->getTimeZone()->willReturn($feedConfig->timezone);
    $leaveFeedData->getStartDate()->willReturn(new DateTime('2018-06-01'));
    $leaveFeedData->getEndDate()->willReturn(new DateTime('2018-09-01'));
    $leaveFeedData->get()->willReturn($sampleData);

    $feedIcal = new LeaveRequestCalendarFeedIcal();
    $feedIcal = $feedIcal->get($leaveFeedData->reveal());

    $expectedIcal = $this->getIcalHeaderForMonterreyTimezoneAndCurrentYearIs2018() .
      $this->getIcalBody($dateTime, $sampleData);
    $this->assertEquals($expectedIcal, $feedIcal);
  }

  private function getIcalBody($instantiatedDateTime, $leaveData) {
    foreach($leaveData as $data) {
      $fromDate = new DateTime($data['from_date']);
      $toDate = new DateTime($data['to_date']);

      $icalData[] = 'BEGIN:VEVENT';
      $icalData[] = 'SUMMARY:' . $data['display_name'];
      $icalData[] = 'DTSTART:' . $fromDate->format('Ymd\THis');
      $icalData[] = 'DTEND:' . $toDate->format('Ymd\THis');
      $icalData[] = 'UID:' . $data['id'];
      $icalData[] = 'DTSTAMP:' . $instantiatedDateTime->format('Ymd\THis');
      $icalData[] = 'END:VEVENT';
    }

    $icalData[] = 'END:VCALENDAR' ."\r\n";
    return implode("\r\n", $icalData);
  }

  private function getIcalHeaderForMonterreyTimezoneAndCurrentYearIs2018() {
    $header = [
      'BEGIN:VCALENDAR',
      'VERSION:2.0',
      'PRODID:-//ZContent.net//ZapCalLib 1.0//EN',
      'CALSCALE:GREGORIAN',
      'METHOD:PUBLISH',
      'BEGIN:VTIMEZONE',
      'TZID:America/Monterrey',
      'BEGIN:DAYLIGHT',
      'DTSTART:20170402T030000',
      'TZOFFSETFROM:-0600',
      'TZOFFSETTO:-0500',
      'TZNAME:CDT',
      'END:DAYLIGHT',
      'BEGIN:STANDARD',
      'DTSTART:20171029T010000',
      'TZOFFSETFROM:-0500',
      'TZOFFSETTO:-0600',
      'TZNAME:CST',
      'END:STANDARD',
      'BEGIN:DAYLIGHT',
      'DTSTART:20180401T030000',
      'TZOFFSETFROM:-0600',
      'TZOFFSETTO:-0500',
      'TZNAME:CDT',
      'END:DAYLIGHT',
      'BEGIN:STANDARD',
      'DTSTART:20181028T010000',
      'TZOFFSETFROM:-0500',
      'TZOFFSETTO:-0600',
      'TZNAME:CST',
      'END:STANDARD',
      'BEGIN:DAYLIGHT',
      'DTSTART:20190407T030000',
      'TZOFFSETFROM:-0600',
      'TZOFFSETTO:-0500',
      'TZNAME:CDT',
      'END:DAYLIGHT',
      'BEGIN:STANDARD',
      'DTSTART:20191027T010000',
      'TZOFFSETFROM:-0500',
      'TZOFFSETTO:-0600',
      'TZNAME:CST',
      'END:STANDARD',
      'END:VTIMEZONE'."\r\n",
    ];

    return implode("\r\n", $header);
  }
}
