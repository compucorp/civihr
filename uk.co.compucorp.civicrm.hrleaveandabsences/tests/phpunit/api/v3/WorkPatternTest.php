<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;

/**
 * Class api_v3_WorkPatternTest
 *
 * @group headless
 */
class api_v3_WorkPatternTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_WorkPatternHelpersTrait;

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id, period_id
   */
  public function testGetCalendarRequiresContactIdAndPeriodID() {
    civicrm_api3('WorkPattern', 'getCalendar');
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: contact_id
   */
  public function testGetCalendarRequiresContactIdIfPeriodIDIsNotEmpty() {
    civicrm_api3('WorkPattern', 'getCalendar', ['period_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: period_id
   */
  public function testGetCalendarRequiresPeriodIdIfContactIDIsNotEmpty() {
    civicrm_api3('WorkPattern', 'getCalendar', ['contact_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Unable to find a CRM_HRLeaveAndAbsences_BAO_AbsencePeriod with id 99989389121.
   */
  public function testGetCalendarThrowsAnErrorIfThePeriodIDIsNotForAnExistentAbsencePeriod() {
    civicrm_api3('WorkPattern', 'getCalendar', ['contact_id' => 1, 'period_id' => 99989389121]);
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
      'period_id' => 1
    ]);
  }

  public function testGetCalendarShouldReturnEmptyIfTheGivenContactDoesntExists() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-05'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-19'),
    ]);

    $result = civicrm_api3('WorkPattern', 'getCalendar', ['contact_id' => 321, 'period_id' => $absencePeriod->id]);
    $this->assertEmpty($result['values'][0]['calendar']);
  }

  /**
   * Just an small test to make sure it can call the service and return the dates
   */
  public function testGetCalendarCanReturnCalendarsForMultipleContacts() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();

    // 15 days absence period
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-05'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-19'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    // contracts covering the whole period
    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact1['id']],
      [
        'period_start_date' => '2015-01-05',
        'period_end_date' => '2015-01-19'
      ]
    );

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact2['id']],
      [
        'period_start_date' => '2015-01-05',
        'period_end_date' => '2015-01-19'
      ]
    );

    $workDayTypes = $this->getWorkDayTypeOptionsArray();

    $calendars = civicrm_api3('WorkPattern', 'getCalendar', [
      'period_id' => $absencePeriod->id,
      'contact_id' => ['IN' => [$contact1['id'], $contact2['id']]]
    ])['values'];

    $this->assertCount(2, $calendars);

    // Since there's not guarantee about the order on which the calendars will
    // be returned, we get all the contacts ID's from the result, put them in an
    // array and then check if this array only contains the IDs we're expecting
    $returnedContactsIDs = array_column($calendars, 'contact_id');
    $this->assertContains($contact1['id'], $returnedContactsIDs);
    $this->assertContains($contact2['id'], $returnedContactsIDs);

    // Asserting ALL the dates would make the test too big, so we assert the
    // total number of dates, the first and last ones and a few dates in the
    // middle, assuming all the other will be correct.
    // Since both contacts have the same work pattern, both calendars should be equal
    foreach($calendars as $calendar) {
      $this->assertEquals('2015-01-05', $calendar['calendar'][0]['date']);
      $this->assertEquals($workDayTypes['working_day'], $calendar['calendar'][0]['type']);
      $this->assertEquals('2015-01-08', $calendar['calendar'][3]['date']);
      $this->assertEquals($workDayTypes['working_day'], $calendar['calendar'][3]['type']);
      $this->assertEquals('2015-01-11', $calendar['calendar'][6]['date']);
      $this->assertEquals($workDayTypes['weekend'], $calendar['calendar'][6]['type']);
      $this->assertEquals('2015-01-16', $calendar['calendar'][11]['date']);
      $this->assertEquals($workDayTypes['working_day'], $calendar['calendar'][11]['type']);
      $this->assertEquals('2015-01-19', $calendar['calendar'][14]['date']);
      $this->assertEquals($workDayTypes['working_day'], $calendar['calendar'][14]['type']);
    }

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
