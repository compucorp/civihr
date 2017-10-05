<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_BAO_WorkWeek as WorkWeek;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException as InvalidWorkDayException;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_WorkDayTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_WorkDayTest extends BaseHeadlessTest {

  protected $workPattern = null;
  protected $workWeek = null;

  public function setUp() {
    parent::setUp();
    $this->instantiateWorkPatternWithWeek();
  }

  /**
   * @dataProvider dayOfTheWeekDataProvider
   */
  public function testDayOfTheWeekShouldBeValidAccordingToISO8601($day, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
        InvalidWorkDayException::class,
        'Day of the Week should be a number between 1 and 7, according to ISO-8601'
      );
    }

    $this->createBasicWorkDay(['day_of_the_week' => $day]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Time From format should be hh:mm
   *
   * @dataProvider timeFormatDataProvider
   */
  public function testTimeFromFormatShouldBeValid($time) {
    $this->createBasicWorkDay(['time_from' => $time]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Time To format should be hh:mm
   *
   * @dataProvider timeFormatDataProvider
   */
  public function testTimeToFormatShouldBeValid($time) {
    $this->createBasicWorkDay(['time_to' => $time]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Time From, Time To, Break and Number of Hours are required for Working Days
   *
   * @dataProvider timesAndBreakRequiredDataProvider
   */
  public function testTimeFromTimeToAndBreakShouldBeRequiredIfItsAWorkingDay($timeTo, $timeFrom, $break) {
    $params = [
      'type' => WorkDay::getWorkingDayTypeValue(),
      'time_to' => $timeTo,
      'time_from' => $timeFrom,
      'break' => $break
    ];
    $this->createBasicWorkDay($params);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Break should be a valid number
   *
   * @dataProvider invalidFormatBreakDataProvider
   */
  public function testBreakMustBeANumber ($break) {
    $entity = $this->createBasicWorkDay(['break' => $break]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Time From should be less than Time To
   *
   * @dataProvider timeFromGreaterThanTimeToDataProvider
   */
  public function testTimeFromShouldNotBeGreaterOrEqualThanTimeTo($timeFrom, $timeTo) {
    $this->createBasicWorkDay([
      'time_from' => $timeFrom,
      'time_to' => $timeTo
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Break cannot be less than 0
   */
  public function testBreakCannotBeLessThan0() {
    $this->createBasicWorkDay(['break' => -1]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Break should be less than the number of hours between Time From and Time To
   *
   * @dataProvider breakGreaterThanWorkingHours
   */
  public function testBreakShouldNotBeGreaterThenThePeriodBetweenTimeFromAndTimeTo($timeFrom, $timeTo, $break) {
    $params = [
      'type' => CRM_HRLeaveAndAbsences_BAO_WorkDay::getWorkingDayTypeValue(),
      'time_to' => $timeTo,
      'time_from' => $timeFrom,
      'break' => $break
    ];
    $this->createBasicWorkDay($params);
  }

  /**
   * @dataProvider workDayTypeDataProvider
   */
  public function testTypeShouldBeAValidValueInWorkDayTypeOptions($type, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(InvalidWorkDayException::class, 'Invalid Work Day Type');
    }

    // If type is not Working Day, we must set times, break and number of hours to null,
    // otherwise we will get another validation error
    $params = ['type' => $type];
    if($type != WorkDay::getWorkingDayTypeValue()) {
      $params['time_from'] = null;
      $params['time_to'] = null;
      $params['break'] = null;
      $params['number_of_hours'] = null;
    }
    $this->createBasicWorkDay($params);
  }

  public function testWorkWeekCannotBeChangedOnUpdate() {
    $entity = $this->createBasicWorkDay();
    $this->assertEquals($this->workWeek->id, $entity->week_id);

    $updatedEntity = $this->updateWorkDay($entity->id, [
      'week_id' => rand(100, 200)
    ]);
    $this->assertEquals($this->workWeek->id, $updatedEntity->week_id);
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testCannotHaveMoreThanOfDayOfTheWeekForEachWeek() {
    $entity = $this->createBasicWorkDay(['day_of_the_week' => 1]);
    $this->assertEquals(1, $entity->day_of_the_week);

    $this->createBasicWorkDay(['day_of_the_week' => 1]);
  }

  /**
   * @dataProvider validNumberOfHoursDataProvider
   */
  public function testNumberOfHours($numberOfHours) {
    $entity = $this->createBasicWorkDay(['number_of_hours' => $numberOfHours]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Number of Hours should be a valid number
   *
   * @dataProvider invalidFormatNumberOfHoursDataProvider
   */
  public function testNumberOfHoursMustBeANumber ($numberOfHours) {
    $entity = $this->createBasicWorkDay(['number_of_hours' => $numberOfHours]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Number of Hours should be between 0 and 24
   *
   * @dataProvider outOfBoundsNumberOfHoursDataProvider
   */
  public function testNumberOfHoursMustBeBetween0And24 ($numberOfHours) {
    $entity = $this->createBasicWorkDay(['number_of_hours' => $numberOfHours]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkDayException
   * @expectedExceptionMessage Number of Hours should be divisible by 0.25
   */
  public function testNumberOfHoursMustBeDivisableBy15Minutes () {
    $entity = $this->createBasicWorkDay(['number_of_hours' => '7.2']);
  }

  public function testGetNonWorkingDayTypeValueReturnsTheOptionValueValueAsString() {
    $workDayTypes = array_flip(WorkDay::buildOptions('type', 'validate'));
    $this->assertSame((string)$workDayTypes['non_working_day'], WorkDay::getNonWorkingDayTypeValue());
  }

  public function testGetWorkingDayTypeValueReturnsTheOptionValueValueAsString() {
    $workDayTypes = array_flip(WorkDay::buildOptions('type', 'validate'));
    $this->assertSame((string)$workDayTypes['working_day'], WorkDay::getWorkingDayTypeValue());
  }

  public function testGetWeekendTypeValueReturnsTheOptionValueValueAsString() {
    $workDayTypes = array_flip(WorkDay::buildOptions('type', 'validate'));
    $this->assertSame((string)$workDayTypes['weekend'], WorkDay::getWeekendTypeValue());
  }

  public function testCreateWorkDayWithWithRandomDecimalValuesAsLeaveDays() {
    $leaveDayAmounts = [0.5, 1.5, 2.5];
    $this->createBasicWorkDay(['day_of_the_week' => 1, 'leave_days' => $leaveDayAmounts[0]]);
    $this->createBasicWorkDay(['day_of_the_week' => 2, 'leave_days' => $leaveDayAmounts[1]]);
    $this->createBasicWorkDay(['day_of_the_week' => 3, 'leave_days' => $leaveDayAmounts[2]]);

    $workDays = new WorkDay();
    $workDays->week_id = $this->workWeek->id;
    $workDays->find();

    //Three Work days were created
    $this->assertEquals($workDays->N, 3);

    while($workDays->fetch()) {
      $leaveDays[] = $workDays->leave_days;
    }

    //compare that the leave day amount we created the work days with is the same as what is in the db
    sort($leaveDays);
    $this->assertEquals($leaveDays, $leaveDayAmounts);
  }

  private function createBasicWorkDay($params = []) {
    $basicDefaultParams = [
      'day_of_the_week' => 1,
      'type' => WorkDay::getWorkingDayTypeValue(),
      'week_id' => $this->workWeek->id,
      'time_from' => '09:00',
      'time_to' => '18:00',
      'break' => 1,
      'leave_days' => 1,
      'number_of_hours' => 8
    ];

    $params = array_merge($basicDefaultParams, $params);
    return WorkDay::create($params);
  }

  private function updateWorkDay($id, $params) {
    $params['id'] = $id;
    $this->createBasicWorkDay($params);

    return WorkDay::findById($id);
  }

  private function instantiateWorkPatternWithWeek() {
    $this->workPattern = WorkPattern::create([
      'label' => 'Pattern ' . microtime()
    ]);

    $this->instantiateWorkWeek($this->workPattern->id);
  }

  private function instantiateWorkWeek($patternId) {
    $this->workWeek = WorkWeek::create([
      'pattern_id' => $patternId
    ]);
  }

  public function dayOfTheWeekDataProvider() {
    return [
      ['a', true],
      [-10, true],
      [-1, true],
      [0, true],
      [1, false],
      [2, false],
      [3, false],
      [4, false],
      [5, false],
      [6, false],
      [7, false],
      [8, true],
      [rand(9, 1000), true],
      [rand(1001, 2000), true],
    ];
  }

  public function timeFromGreaterThanTimeToDataProvider() {
    return [
      ['19:00', '08:00'],
      ['19:00', '18:00'],
      ['19:00', '19:00'],
      ['01:00', '00:00'],
      ['17:31', '17:30'],
    ];
  }

  public function timesAndBreakRequiredDataProvider() {
    return [
      [null, null, null],
      [null, null, 1],
      [null, '19:00', null],
      ['17:00', null, null],
      ['09:00', '17:30', null],
      [null, '17:30', 2],
      ['11:30', null, 2],
    ];
  }

  public function timesAndBreakEmptyDataProvider() {
    return [
      [null, null, 1],
      [null, '19:00', null],
      ['17:00', null, null],
      ['09:00', '17:30', null],
      [null, '17:30', 2],
      ['11:30', null, 2],
      ['09:00', '18:00', 1]
    ];
  }

  public function timeFormatDataProvider() {
    return [
      ['19'],
      ['dasdasdas'],
      [1],
      ['1:00'],
    ];
  }

  public function breakGreaterThanWorkingHours() {
    return [
      ['10:00', '11:30', 2],
      ['09:15', '15:15', 7.5],
      ['12:30', '17:30', 6],
      ['14:00', '16:00', 2],
    ];
  }

  public function workDayTypeDataProvider() {
    return [
      [0, true],
      [WorkDay::getWorkingDayTypeValue(), false],
      [WorkDay::getNonWorkingDayTypeValue(), false],
      [WorkDay::getWeekendTypeValue(), false],
      ['adasdsa', true],
      [rand(4, PHP_INT_MAX), true],
    ];
  }

  public function invalidFormatBreakDataProvider () {
    return [['some_string'], [true]];
  }

  public function validNumberOfHoursDataProvider () {
    return [[8], [7.5], [5], [3.75], [7.25]];
  }

  public function invalidFormatNumberOfHoursDataProvider () {
    return [['some_string'], [true]];
  }

  public function outOfBoundsNumberOfHoursDataProvider () {
    return [[-0.25], [24.25]];
  }
}
