<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_Service_JobContract as JobContractService;
use CRM_HRLeaveAndAbsences_Service_WorkPatternCalendar as WorkPatternCalendarService;
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

  public function testGetCalendarShouldReturnEmptyIfTheGivenContactDoesntExists() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-05'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-19'),
    ]);

    $result = civicrm_api3('WorkPattern', 'getCalendar', ['contact_id' => 321, 'period_id' => $absencePeriod->id]);
    $this->assertEmpty($result['values']);
  }

  /**
   * Just an small test to make sure it can call the service and return the dates
   */
  public function testGetCalendarUsesTheWorkPatternCalendarService() {
    $contact = ContactFabricator::fabricate();

    // 15 days absence period
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-05'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-19'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);
    $multipleWeekWorkPattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    // contract covers the whole period
    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      [
        'period_start_date' => '2015-01-05',
        'period_end_date' => '2015-01-19'
      ]
    );

    // work pattern is effective only after the contract end
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $multipleWeekWorkPattern->id,
      'effective_date' => CRM_Utils_Date::processDate('2015-02-05'),
    ]);

    $workDayTypes = $this->getWorkDayTypeOptionsArray();

    $calendarDates = civicrm_api3('WorkPattern', 'getCalendar', [
      'period_id' => $absencePeriod->id,
      'contact_id' => $contact['id']
    ])['values'];

    // Asserting ALL the dates would make the test too big, so we assert the
    // total number of dates, the first and last ones and a few dates in the
    // middle, assuming all the other will be correct.
    $this->assertCount(15, $calendarDates);
    $this->assertEquals('2015-01-05', $calendarDates[0]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[0]['type']);
    $this->assertEquals('2015-01-08', $calendarDates[3]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[3]['type']);
    $this->assertEquals('2015-01-11', $calendarDates[6]['date']);
    $this->assertEquals($workDayTypes['weekend'], $calendarDates[6]['type']);
    $this->assertEquals('2015-01-16', $calendarDates[11]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[11]['type']);
    $this->assertEquals('2015-01-19', $calendarDates[14]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[14]['type']);
  }
}
