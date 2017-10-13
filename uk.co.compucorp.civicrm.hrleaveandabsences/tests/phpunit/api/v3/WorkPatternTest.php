<?php

use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;

/**
 * Class api_v3_WorkPatternTest
 *
 * @group headless
 */
class api_v3_WorkPatternTest extends BaseHeadlessTest {

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id, start_date, end_date
   */
  public function testGetCalendarRequiresContactIdAndStartAndEndDates() {
    civicrm_api3('WorkPattern', 'getCalendar');
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id
   */
  public function testGetCalendarRequiresContactIdIfStartAndEndDatesAreNotEmpty() {
    civicrm_api3('WorkPattern', 'getCalendar', ['start_date' => '2016-06-03', 'end_date' => '2016-06-05']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: start_date
   */
  public function testGetCalendarRequiresStartDateIfContactIDAndEndDateAreNotEmpty() {
    civicrm_api3('WorkPattern', 'getCalendar', ['contact_id' => 1, 'end_date' => '2016-06-03']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: end_date
   */
  public function testGetCalendarRequiresEndDateIfContactIDAndStartDateAreNotEmpty() {
    civicrm_api3('WorkPattern', 'getCalendar', ['contact_id' => 1, 'start_date' => '2016-06-03']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage The contact_id parameter only supports the IN operator
   *
   * @dataProvider invalidGetCalendarContactIDOperators
   */
  public function testGetCalendarContactIDOnlyAllowTheINOperator($operator) {
    civicrm_api3('WorkPattern', 'getCalendar', [
      'contact_id' => [$operator => [1]],
      'start_date' => '2016-06-03',
      'end_date' => '2016-06-05'
    ]);
  }

  /**
   * Just an small test to make sure it can call the service and return the dates
   */
  public function testGetCalendarCanReturnCalendarsForMultipleContacts() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);
    $workPattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    // contracts covering the whole period
    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2015-01-05',
        'period_end_date' => '2015-01-08'
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      [
        'period_start_date' => '2015-01-05',
        'period_end_date' => '2015-01-08'
      ]
    );

    ContactWorkPatternFabricator::fabricate([
      'pattern_id' => $workPattern->id,
      'contact_id' => $contact2['id']
    ]);

    $workDayTypes = array_flip(WorkDay::buildOptions('type', 'validate'));

    // 15 days interval
    $startDate = '2015-01-05';
    $endDate = '2015-01-08';

    $calendars = civicrm_api3('WorkPattern', 'getCalendar', [
      'start_date' => $startDate,
      'end_date' => $endDate,
      'contact_id' => ['IN' => [$contact1['id'], $contact2['id']]]
    ])['values'];

    $this->assertCount(2, $calendars);

    // Since there's not guarantee about the order on which the calendars will
    // be returned, we get all the contacts ID's from the result, put them in an
    // ordered array and then check if this array have is equal to the contact
    // id we passed to the API
    $returnedContactsIDs = array_column($calendars, 'contact_id');
    sort($returnedContactsIDs);
    $this->assertEquals([$contact1['id'], $contact2['id']], $returnedContactsIDs);

    $expectedContact1Calendar = [
      [
        'date' => '2015-01-05',
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2015-01-06',
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2015-01-07',
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2015-01-08',
        'type' => $workDayTypes['working_day']
      ],
    ];

    $expectedContact2Calendar = [
      [
        'date' => '2015-01-05',
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2015-01-06',
        'type' => $workDayTypes['non_working_day']
      ],
      [
        'date' => '2015-01-07',
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2015-01-08',
        'type' => $workDayTypes['non_working_day']
      ],
    ];

    $contact1Calendar = $this->getCalendarByContactID($calendars, $contact1['id']);
    $contact2Calendar = $this->getCalendarByContactID($calendars, $contact2['id']);

    $this->assertEquals($expectedContact1Calendar, $contact1Calendar);
    $this->assertEquals($expectedContact2Calendar, $contact2Calendar);
  }

  /**
   * Given a list of calendars returned by WorkPattern.getCalendar, returns the
   * one beloging to the contact with the given ID
   *
   * @param array $calendars
   * @param int $contactID
   *
   * @return null
   */
  private function getCalendarByContactID($calendars, $contactID) {
    foreach($calendars as $calendar) {
      if ($calendar['contact_id'] == $contactID) {
        return $calendar['calendar'];
      }
    }

    return null;
  }

  public function invalidGetCalendarContactIDOperators() {
    return [
      ['>'],
      ['>='],
      ['<='],
      ['<'],
      ['<>'],
      ['!='],
      ['BETWEEN'],
      ['NOT BETWEEN'],
      ['LIKE'],
      ['NOT LIKE'],
      ['NOT IN'],
      ['IS NULL'],
      ['IS NOT NULL'],
    ];
  }
}
