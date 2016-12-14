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
 * Class CRM_HRLeaveAndAbsences_Service_WorkPatternCalendar
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_WorkPatternCalendarTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_WorkPatternHelpersTrait;

  private $contact;
  private $jobContractService;

  public function setUp() {
    $this->contact = ContactFabricator::fabricate();
    $this->jobContractService = new JobContractService();

    // We delete everything to avoid problems with the default work pattern
    // created during the extension installation
    $tableName = WorkPattern::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");
  }

  public function testGetShouldReturnEmptyForContactWithoutContractOnThePeriod() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-12-31'),
    ]);

    $calendar = new WorkPatternCalendarService($this->contact['id'], $absencePeriod, $this->jobContractService);
    $this->assertEmpty($calendar->get());
  }

  public function testGetShouldUseTheDefaultWorkPatternIfTheContactHasNoActiveWorkPatternDuringTheContract() {
    // 15 days absence period
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-05'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-19'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);
    $multipleWeekWorkPattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    // contract covers the whole period
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2015-01-05',
        'period_end_date' => '2015-01-19'
      ]
    );

    // work pattern is effective only after the contract end
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->contact['id'],
      'pattern_id' => $multipleWeekWorkPattern->id,
      'effective_date' => CRM_Utils_Date::processDate('2015-02-05'),
    ]);

    $calendar = new WorkPatternCalendarService($this->contact['id'], $absencePeriod, $this->jobContractService);

    $workDayTypes = $this->getWorkDayTypeOptionsArray();

    $calendarDates = $calendar->get();
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

  public function testGetShouldUseTheDefaultWorkPatternIfTheAssignedWorkPatternDoesntCoverTheWholeContractPeriod() {
    // 31 days absence period
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-31'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);
    $multipleWeekWorkPattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    // contract covers the whole period
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2015-01-01',
        'period_end_date' => '2015-01-31'
      ]
    );

    // work pattern is effective only at the middle of the contract
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->contact['id'],
      'pattern_id' => $multipleWeekWorkPattern->id,
      'effective_date' => CRM_Utils_Date::processDate('2015-01-12'),
    ]);

    $calendar = new WorkPatternCalendarService($this->contact['id'], $absencePeriod, $this->jobContractService);

    $workDayTypes = $this->getWorkDayTypeOptionsArray();

    $calendarDates = $calendar->get();
    // Asserting ALL the dates would make the test too big, so we assert the
    // total number of dates, the first and last ones and a few dates in the
    // middle, assuming all the other will be correct.
    $this->assertCount(31, $calendarDates);
    $this->assertEquals('2015-01-01', $calendarDates[0]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[0]['type']);

    $this->assertEquals('2015-01-06', $calendarDates[5]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[5]['type']);

    $this->assertEquals('2015-01-07', $calendarDates[6]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[6]['type']);

    // this is a tuesday, which should be a working day on the default work pattern,
    // but the multiple day work pattern became effective on 01-12, and tuesdays
    // are non working days on it's first week
    $this->assertEquals('2015-01-13', $calendarDates[12]['date']);
    $this->assertEquals($workDayTypes['non_working_day'], $calendarDates[12]['type']);

    // the next tuesday after 01-13 is 01-20, which is a working day on the second
    // week of the work pattern
    $this->assertEquals('2015-01-20', $calendarDates[19]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[19]['type']);

    // and then the next tuesday is a non-working day again, because we're using
    // the first week of the work pattern again
    $this->assertEquals('2015-01-27', $calendarDates[26]['date']);
    $this->assertEquals($workDayTypes['non_working_day'], $calendarDates[26]['type']);

    $this->assertEquals('2015-01-31', $calendarDates[30]['date']);
    $this->assertEquals($workDayTypes['weekend'], $calendarDates[30]['type']);
  }

  public function testGetShouldOnlyReturnDatesWithinTheContactContractsDuringThePeriod() {
    // 31 days absence period
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-31'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    // create two contracts within the absence period, but with a gap
    // between them.
    // This first one has only 10 days
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2015-01-01',
        'period_end_date' => '2015-01-10'
      ]
    );

    // This second contract has 17 days
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2015-01-15',
        'period_end_date' => '2015-01-31'
      ]
    );

    $calendar = new WorkPatternCalendarService($this->contact['id'], $absencePeriod, $this->jobContractService);
    $calendarDates = $calendar->get();

    // 27 (10 from one contract + 17 from the other) instead of 31 for the whole period
    $this->assertCount(27, $calendarDates);

    $dates = array_column($calendarDates, 'date');
    // Again, to avoid a huge test, we only assert for boundary dates and the
    // gap dates
    $this->assertContains('2015-01-01', $dates);
    $this->assertContains('2015-01-10', $dates);
    $this->assertNotContains('2015-01-11', $dates);
    $this->assertNotContains('2015-01-12', $dates);
    $this->assertNotContains('2015-01-13', $dates);
    $this->assertNotContains('2015-01-14', $dates);
    $this->assertContains('2015-01-15', $dates);
    $this->assertContains('2015-01-31', $dates);
  }

  public function testGetCanGenerateCalendarForAContractWhichStartedBeforeThePeriodStartDateWithDefaultWorkPattern() {
    // 10 days absence period
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-10'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2014-12-25',
        'period_end_date' => '2015-01-10'
      ]
    );

    $calendar = new WorkPatternCalendarService($this->contact['id'], $absencePeriod, $this->jobContractService);
    $calendarDates = $calendar->get();

    $this->assertCount(10, $calendarDates);
    $this->assertEquals('2015-01-01', $calendarDates[0]['date']);
    $this->assertEquals('2015-01-01', $calendarDates[0]['date']);
    $this->assertEquals('2015-01-10', $calendarDates[9]['date']);
  }

  public function testGetCanCycleTheWeeksOfAWorkPatternEffectiveBeforeTheAbsencePeriod() {
    // 10 days absence period
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-10'),
    ]);

    $workPattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours(['is_default' => true]);

    // Make the pattern effective on the previous absence period
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->contact['id'],
      'pattern_id' => $workPattern->id,
      'effective_date' => CRM_Utils_Date::processDate('2014-12-25'),
    ]);

    // The contract also starts on the previous year
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2014-12-25',
        'period_end_date' => '2015-01-10'
      ]
    );

    $calendar = new WorkPatternCalendarService($this->contact['id'], $absencePeriod, $this->jobContractService);
    $calendarDates = $calendar->get();

    $workDayTypes = $this->getWorkDayTypeOptionsArray();

    $this->assertCount(10, $calendarDates);
    // even though the period starts on 2015-01-01, the weeks cycle starts at the
    // work patter effective date, 2014-12-25. This puts 2015-01-01 on the second
    // week. That day is a thursday, which is a working day on the second week
    $this->assertEquals('2015-01-01', $calendarDates[0]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[0]['type']);

    // 2015-01-02 is a friday, which is a non-working day on the second week
    $this->assertEquals('2015-01-02', $calendarDates[1]['date']);
    $this->assertEquals($workDayTypes['non_working_day'], $calendarDates[1]['type']);

    // 2015-01-09 is ne next friday after 02-01, so we cycle back to the pattern
    // first week. On it, fridays are working days
    $this->assertEquals('2015-01-09', $calendarDates[8]['date']);
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[8]['type']);

    // just checking that the last day is present
    $this->assertEquals('2015-01-10', $calendarDates[9]['date']);
    $this->assertEquals($workDayTypes['weekend'], $calendarDates[9]['type']);
  }

  public function testGetUsesTheContractStartDateToCycleWeeksIfTheWorkPatternEffectiveDateIsBeforeTheContractStartDate() {
    // A period with 2 weeks
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-05'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-19'),
    ]);

    $workPattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours(['is_default' => true]);

    // this contract covers the first week of the period
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2015-01-05',
        'period_end_date' => '2015-01-11'
      ]
    );

    // this contract covers the second week of the period
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2015-01-12',
        'period_end_date' => '2015-01-18'
      ]
    );

    //For the first contract, the patter will be effective on the same date as
    // its start date, but for the second one, it will be effective before
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->contact['id'],
      'pattern_id' => $workPattern->id,
      'effective_date' => CRM_Utils_Date::processDate('2015-01-01'),
    ]);

    $calendar = new WorkPatternCalendarService($this->contact['id'], $absencePeriod, $this->jobContractService);
    $calendarDates = $calendar->get();

    $workDayTypes = $this->getWorkDayTypeOptionsArray();

    $this->assertCount(14, $calendarDates);
    $this->assertEquals('2015-01-05', $calendarDates[0]['date']);
    // the first day, a monday, is using the first week, so it's a working day
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[0]['type']);

    $this->assertEquals('2015-01-09', $calendarDates[4]['date']);
    // 01-09 is a friday on the first week, so it's also a working day
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[4]['type']);

    $this->assertEquals('2015-01-12', $calendarDates[7]['date']);
    // 01-12 is a monday and it's on the second week since the pattern effective
    // date, but since it's within another contract, we start counting it again
    // from first week instead of the second week, meaning it's going to be a
    // working day
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[7]['type']);

    $this->assertEquals('2015-01-16', $calendarDates[11]['date']);
    // and the same way, 01-16, a friday, will also be a working day
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[11]['type']);
  }

  public function testGetUsesThePeriodEndDateWhenTheContractDoesntHaveAndEndDate() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-05'),
    ]);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => true]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2015-01-02'
      ]
    );

    $calendar = new WorkPatternCalendarService($this->contact['id'], $absencePeriod, $this->jobContractService);
    $calendarDates = $calendar->get();

    $dates = array_column($calendarDates, 'date');
    // Even though the contract doesn't have an end date, we'll only return the
    // dates within the absence period, from 2015-01-02 (contract start) to
    // 2015-01-05 (period end)
    $this->assertCount(4, $calendarDates);
    $this->assertNotContains('2015-01-01', $dates);
    $this->assertContains('2015-01-02', $dates);
    $this->assertContains('2015-01-03', $dates);
    $this->assertContains('2015-01-04', $dates);
    $this->assertContains('2015-01-05', $dates);
    $this->assertNotContains('2015-01-06', $dates);
  }

  public function testItCanGenerateTheCalendarWhenThereAreMultiplesWorkPatternsWithinASingleContractStartAndEndDates() {
    // a 19 days period
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2015-01-05'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-23'),
    ]);

    $workPattern1 = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    $workPattern2 = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->contact['id'],
      'pattern_id' => $workPattern1->id,
      'effective_date' => CRM_Utils_Date::processDate('2015-01-01'),
    ]);

    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $this->contact['id'],
      'pattern_id' => $workPattern2->id,
      'effective_date' => CRM_Utils_Date::processDate('2015-01-07'),
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->contact['id']],
      [
        'period_start_date' => '2015-01-05',
        'period_end_date' => '2015-01-16'
      ]
    );

    $calendar = new WorkPatternCalendarService($this->contact['id'], $absencePeriod, $this->jobContractService);
    $calendarDates = $calendar->get();

    $workDayTypes = $this->getWorkDayTypeOptionsArray();

    $this->assertCount(12, $calendarDates);

    $this->assertEquals('2015-01-05', $calendarDates[0]['date']);
    // a monday on the first week. The 40 hours work pattern was effective on this
    // date, so it's a working day
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[0]['type']);

    $this->assertEquals('2015-01-08', $calendarDates[3]['date']);
    // a thursday on the first week. But now, the 31 and 1/4 hours work pattern is
    // effective, so we use that pattern first week and a thursday is a non
    // working day on it
    $this->assertEquals($workDayTypes['non_working_day'], $calendarDates[3]['type']);

    $this->assertEquals('2015-01-09', $calendarDates[4]['date']);
    // And the next day, a friday, will be a working day
    $this->assertEquals($workDayTypes['working_day'], $calendarDates[4]['type']);

    $this->assertEquals('2015-01-16', $calendarDates[11]['date']);
    // This is a friday. We keep using that work pattern for the next week. So, now we use its
    // second week and a friday is a non working day on it
    $this->assertEquals($workDayTypes['non_working_day'], $calendarDates[11]['type']);
  }
}
