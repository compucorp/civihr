<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Service_ContactWorkPattern as ContactWorkPatternService;


/**
 * Class CRM_HRLeaveAndAbsences_Service_ContactWorkPatternTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_ContactWorkPatternTest extends BaseHeadlessTest {

  private $contactWorkPatternService;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $tableName = WorkPattern::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$tableName}");
    $this->contactWorkPatternService = new ContactWorkPatternService();
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 1;');
  }


  public function testGetContactWorkDayForDateReturnsCorrectlyForAContactUsingTheDefaultWorkPattern() {
    $periodStartDate = new DateTime('2017-01-01');

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2017-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2017-12-31')
    ]);

    $contract = HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [ 'period_start_date' => $periodStartDate->format('Y-m-d') ]
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);
    $workWeeks = WorkPatternFabricator::getWeekFor40HourWorkWeek();

    //2017-01-02 is a monday and a working day
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2017-01-02'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks['days'][0]);
    $this->assertEquals($expectedWorkDay, $workDay);

    //2017-01-06 is a friday and a working day
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2017-01-06'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks['days'][4]);
    $this->assertEquals($expectedWorkDay, $workDay);

    //2017-01-07 is a saturday and a weekend
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2017-01-07'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks['days'][5]);
    $this->assertEquals($expectedWorkDay, $workDay);
  }

  public function testGetContactWorkDayForDateReturnsCorrectlyForAContactWithWorkPatternHavingOneWeek() {
    $periodStartDate = new DateTime('2016-07-01');

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-07-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31')
    ]);

    $contract = HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [ 'period_start_date' => $periodStartDate->format('Y-m-d') ]
    );

    $pattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();

    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'pattern_id' => $pattern->id,
      'effective_date' => $periodStartDate->format('YmdHis')
    ]);

    $workWeeks = WorkPatternFabricator::getWeekFor40HourWorkWeek();

    //2016-07-04 is a monday and a working day
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2016-07-04'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks['days'][0]);
    $this->assertEquals($expectedWorkDay, $workDay);

    //2016-07-08 is a friday and a working day
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2016-07-08'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks['days'][4]);
    $this->assertEquals($expectedWorkDay, $workDay);

    //2016-07-09 is a saturday and a weekend
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2016-07-09'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks['days'][5]);
    $this->assertEquals($expectedWorkDay, $workDay);
  }

  public function testGetContactWorkDayForDateReturnsCorrectlyForAContactWithWorkPatternHavingMoreThanOneWeek() {
    $periodStartDate = new DateTime('2017-07-31');

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2017-07-31'),
      'end_date' => CRM_Utils_Date::processDate('2017-12-31')
    ]);

    $contract = HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [ 'period_start_date' => $periodStartDate->format('Y-m-d') ]
    );

    $pattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contract['contact_id'],
      'pattern_id' => $pattern->id,
      'effective_date' => $periodStartDate->format('YmdHis')
    ]);

    $workWeeks = WorkPatternFabricator::getWeekForTwoWeeksAnd31AndHalfHours();

    //2017-08-04 is a friday on first week and a working day
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2017-08-04'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks[0]['days'][4]);
    $this->assertEquals($expectedWorkDay, $workDay);

    //2017-08-06 is a sunday on first week and non-working day
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2017-08-06'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks[0]['days'][6]);
    $this->assertEquals($expectedWorkDay, $workDay);

    //2017-08-07 is a monday on second week and not a working day
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2017-08-07'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks[1]['days'][0]);
    $this->assertEquals($expectedWorkDay, $workDay);

    //2017-08-08 is a tuesday on second week and a working day
    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2017-08-08'));
    $expectedWorkDay =  $this->getExpectedWorkDayArray($workWeeks[1]['days'][1]);
    $this->assertEquals($expectedWorkDay, $workDay);
  }

  public function testGetContactWorkDayForDateReturnsNullWhenContactHasNoWorkPatternAndThereIsNoDefaultWorkPattern() {
    $periodStartDate = new DateTime('2017-01-01');

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2017-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2017-12-31')
    ]);

    $contract = HRJobContractFabricator::fabricate(
      [ 'contact_id' => 1 ],
      [ 'period_start_date' => $periodStartDate->format('Y-m-d') ]
    );

    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contract['contact_id'], new DateTime('2017-01-02'));
    $this->assertNull($workDay);
  }

  public function testGetContactWorkDayForDateReturnsNullWhenContactHasNoContractAndThereIsDefaultWorkPattern() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2017-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2017-12-31')
    ]);

    $contactID = 1;
    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default' => 1]);

    $workDay = $this->contactWorkPatternService->getContactWorkDayForDate($contactID, new DateTime('2017-01-02'));
    $this->assertNull($workDay);
  }

  private function getExpectedWorkDayArray($workDay) {
    return [
      'day_of_the_week' => $workDay['day_of_the_week'],
      'type' => $workDay['type'],
      'time_from' => CRM_Utils_Array::value('time_from', $workDay, '') ,
      'time_to' => CRM_Utils_Array::value('time_to', $workDay, ''),
      'break' => CRM_Utils_Array::value('break', $workDay, ''),
      'leave_days' => !empty($workDay['leave_days']) ? $workDay['leave_days'] : '',
      'number_of_hours' =>  !empty($workDay['number_of_hours']) ? $workDay['number_of_hours'] : ''
    ];
  }
}
