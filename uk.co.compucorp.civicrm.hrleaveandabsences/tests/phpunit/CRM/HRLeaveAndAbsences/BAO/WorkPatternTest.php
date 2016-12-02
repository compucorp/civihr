<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_WorkPatternTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_WorkPatternTest extends BaseHeadlessTest {

  public function setUp() {
    //Deletes the default work pattern so it doesn't interfere with the tests
    WorkPattern::del(1);
  }

  public function testWeightShouldAlwaysBeMaxWeightPlus1OnCreate() {
    $firstEntity = WorkPatternFabricator::fabricate();
    $this->assertNotEmpty($firstEntity->weight);

    $secondEntity = WorkPatternFabricator::fabricate();
    $this->assertNotEmpty($secondEntity->weight);
    $this->assertEquals($firstEntity->weight + 1, $secondEntity->weight);
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testWorkPatternLabelsShouldBeUnique() {
    WorkPatternFabricator::fabricate(['label' => 'Pattern 1']);
    WorkPatternFabricator::fabricate(['label' => 'Pattern 1']);
  }

  public function testThereShouldBeOnlyOneDefaultTypeOnCreate() {
    $basicEntity = WorkPatternFabricator::fabricate(['is_default' => true]);
    $entity1 = WorkPattern::findById($basicEntity->id);
    $this->assertEquals(1, $entity1->is_default);

    $basicEntity = WorkPatternFabricator::fabricate(['is_default' => true]);
    $entity2 = WorkPattern::findById($basicEntity->id);
    $entity1 = WorkPattern::findById($entity1->id);
    $this->assertEquals(0,  $entity1->is_default);
    $this->assertEquals(1, $entity2->is_default);
  }

  public function testThereShouldBeOnlyOneDefaultTypeOnUpdate() {
    $basicEntity1 = WorkPatternFabricator::fabricate(['is_default' => false]);
    $basicEntity2 = WorkPatternFabricator::fabricate(['is_default' => false]);
    $entity1 = WorkPattern::findById($basicEntity1->id);
    $entity2 = WorkPattern::findById($basicEntity2->id);
    $this->assertEquals(0,  $entity1->is_default);
    $this->assertEquals(0,  $entity2->is_default);

    $this->updateBasicWorkPattern($basicEntity1->id, ['is_default' => true]);
    $entity1 = WorkPattern::findById($basicEntity1->id);
    $entity2 = WorkPattern::findById($basicEntity2->id);
    $this->assertEquals(1, $entity1->is_default);
    $this->assertEquals(0,  $entity2->is_default);

    $this->updateBasicWorkPattern($basicEntity2->id, ['is_default' => true]);
    $entity1 = WorkPattern::findById($basicEntity1->id);
    $entity2 = WorkPattern::findById($basicEntity2->id);
    $this->assertEquals(0,  $entity1->is_default);
    $this->assertEquals(1, $entity2->is_default);
  }

  public function testFindWithNumberOfWeeksAndHours() {
    $workPattern1 = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    $workPattern2 = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    $object = new WorkPattern();
    $object->findWithNumberOfWeeksAndHours();
    $this->assertEquals(2, $object->N);

    $object->fetch();
    $this->assertEquals($workPattern1->label, $object->label);
    $this->assertEquals(1, $object->number_of_weeks);
    $this->assertEquals(40.0, $object->number_of_hours);

    $object->fetch();
    $this->assertEquals($workPattern2->label, $object->label);
    $this->assertEquals(2, $object->number_of_weeks);
    $this->assertEquals(31.5, $object->number_of_hours);
  }

  public function testGetValuesArrayShouldReturnWorkPatternValues() {
    $params = [
        'label' => 'Pattern Label',
        'description' => 'Pattern Description',
        'is_active' => 1,
        'is_default' => 1
    ];
    $entity = WorkPatternFabricator::fabricate($params);
    $values = WorkPattern::getValuesArray($entity->id);
    $this->assertEquals($params['label'], $values['label']);
    $this->assertEquals($params['description'], $values['description']);
    $this->assertEquals($params['is_active'], $values['is_active']);
    $this->assertEquals($params['is_default'], $values['is_default']);
    $this->assertEmpty($values['weeks']);
  }

  public function testGetValuesArrayShouldReturnWorkPatternValuesWithWeeksAndDays() {
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    $values = WorkPattern::getValuesArray($workPattern->id);

    $this->assertEquals($workPattern->label, $values['label']);
    $this->assertCount(1, $values['weeks']);
    $this->assertCount(7, $values['weeks'][0]['days']);
  }

  public function testCanCreateWorkPatternWithWeeksAndDays() {
    $params = [
      'weeks' => [
        [
          'days' => [
            ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 1],
            ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 2],
            ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 3],
            ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 4],
            ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 5],
            ['type' => 3, 'day_of_the_week' => 6],
            ['type' => 3, 'day_of_the_week' => 7],
          ]
        ]
      ]
    ];

    $workPattern = WorkPatternFabricator::fabricate($params);
    $this->assertNotEmpty($workPattern->id);
    $values = WorkPattern::getValuesArray($workPattern->id);
    $this->assertCount(1, $values['weeks']);
    $this->assertCount(7, $values['weeks'][0]['days']);

    $weekDays = $values['weeks'][0]['days'];
    foreach($values['weeks'][0]['days'] as $i => $day) {
      $this->assertEquals($day['type'], $weekDays[$i]['type']);
      $this->assertEquals($day['day_of_the_week'], $weekDays[$i]['day_of_the_week']);
      if($day['type'] == 2) {
        $this->assertEquals($day['time_from'], $weekDays[$i]['time_from']);
        $this->assertEquals($day['time_to'], $weekDays[$i]['time_to']);
        $this->assertEquals($day['break'], $weekDays[$i]['break']);
        $this->assertEquals($day['leave_days'], $weekDays[$i]['leave_days']);
      }
    }
  }

  public function testCanUpdateWorkPatternWithWeeksAndDays() {
    $workPattern = WorkPatternFabricator::fabricate();
    $this->assertNotEmpty($workPattern->id);
    $values = WorkPattern::getValuesArray($workPattern->id);
    $this->assertCount(0, $values['weeks']);

    $params = [
      'weeks' => [
        [
          'days' => [
            ['type' => 2, 'time_from' => '15:00', 'time_to' => '22:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 1],
            ['type' => 2, 'time_from' => '13:00', 'time_to' => '23:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 2],
            ['type' => 2, 'time_from' => '09:00', 'time_to' => '18:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 3],
            ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 4],
            ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 5],
            ['type' => 3, 'day_of_the_week' => 6],
            ['type' => 3, 'day_of_the_week' => 7],
          ]
        ]
      ]
    ];

    $workPattern = $this->updateBasicWorkPattern($workPattern->id, $params);
    $this->assertNotEmpty($workPattern->id);
    $values = WorkPattern::getValuesArray($workPattern->id);
    $this->assertCount(1, $values['weeks']);
    $this->assertCount(7, $values['weeks'][0]['days']);

    $weekDays = $values['weeks'][0]['days'];
    foreach($values['weeks'][0]['days'] as $i => $day) {
      $this->assertEquals($day['type'], $weekDays[$i]['type']);
      $this->assertEquals($day['day_of_the_week'], $weekDays[$i]['day_of_the_week']);
      if($day['type'] == 2) {
        $this->assertEquals($day['time_from'], $weekDays[$i]['time_from']);
        $this->assertEquals($day['time_to'], $weekDays[$i]['time_to']);
        $this->assertEquals($day['break'], $weekDays[$i]['break']);
        $this->assertEquals($day['leave_days'], $weekDays[$i]['leave_days']);
      }
    }
  }

  public function testGetValuesArrayShouldReturnEmptyArrayWhenWorkPatternDoesntExists() {
    $values = WorkPattern::getValuesArray(1);
    $this->assertEmpty($values);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkPatternException
   * @expectedExceptionMessage You cannot disable a Work Pattern if it's the last one
   */
  public function testCannotDisablePatternIfItIsTheLastWorkPatternEnabled() {
    $workPattern = WorkPatternFabricator::fabricate(['is_active' => 1]);
    $this->updateBasicWorkPattern($workPattern->id, ['is_active' => 0]);
  }

  public function testCanDisablePatternIfItIsNotTheLastWorkPatternEnabled() {
    $workPattern1 = WorkPatternFabricator::fabricate(['is_active' => 1]);
    WorkPatternFabricator::fabricate(['is_active' => 1]);

    $this->updateBasicWorkPattern($workPattern1->id, ['is_active' => 0]);

    $workPattern1 = WorkPattern::findById($workPattern1->id);
    $this->assertEquals(0, $workPattern1->is_active);
  }

  public function testGetLeaveDaysForDateShouldReturnZeroIfDateIsNotGreaterThanOrEqualTheStartDate() {
    $pattern = new WorkPattern();

    $start = new DateTime('2016-01-02');

    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-01-01'), $start
    ));
  }

  public function testGetLeaveDaysForDateShouldReturnZeroIfWorkPatternHasNoWeeks() {
    $pattern = new WorkPattern();

    $start = new DateTime('2016-01-01');

    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-01-01'), $start
    ));
  }

  public function testGetLeaveDaysForDateShouldTheNumberOfDaysForPatternsWithOnlyOneWeek() {
    $pattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();

    $start = new DateTime('2016-01-01');

    // A friday
    $this->assertEquals(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-01-01'), $start
    ));

    // A saturday
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-02-13'), $start
    ));

    // A sunday
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-03-06'), $start
    ));

    // A monday
    $this->assertSame(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-04-04'), $start
    ));

    // A tuesday
    $this->assertSame(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-05-24'), $start
    ));

    // A wednesday
    $this->assertSame(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-06-15'), $start
    ));

    // A thursday
    $this->assertSame(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-07-28'), $start
    ));
  }

  public function testGetLeaveDaysForDateShouldTheNumberOfDaysForPatternsWithMultipleWeeks() {
    // Week 1 weekdays: monday, wednesday and friday
    // Week 2 weekdays: tuesday and thursday
    $pattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    $start = new DateTime('2016-07-31'); // A sunday

    // Since the start date is a sunday, the end of the week, the following day
    // (2016-08-01) should be on the second week. Monday of the second week is
    // not a working day, so the leave days should be 0
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-01'), $start
    ));

    // The next day is a tuesday, which is a working day on the second week, so
    // we should return 1
    $this->assertSame(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-02'), $start
    ));

    // Wednesday is not a working day on the second week, so we should return 0
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-03'), $start
    ));

    // Thursday is a working day on the second week, so we should return 1
    $this->assertSame(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-04'), $start
    ));

    // Friday, Saturday and Sunday are not working days on the second week,
    // so we should return 0
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-05'), $start
    ));
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-06'), $start
    ));
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-07'), $start
    ));

    // Now, since we hit sunday, the following day will be on the third week
    // since the start date, but the work pattern only has 2 weeks, so we
    // rotate back to use the week 1 from the pattern

    // Monday is a working day on the first week
    $this->assertSame(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-08'), $start
    ));

    // Tuesday is not a working day on the first week
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-09'), $start
    ));

    // Wednesday is a working day on the first week
    $this->assertSame(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-10'), $start
    ));

    // Thursday is not a working day on the first week
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-11'), $start
    ));

    // Friday is a working day on the first week
    $this->assertSame(1.0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-12'), $start
    ));

    // Saturday and Sunday are not working days on the first week
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-13'), $start
    ));
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-14'), $start
    ));

    // Hit sunday again, so we are now on the fourth week since the start date.
    // The work pattern will rotate and use the week 2

    // Monday is not a working day on week 2, so it should return 0
    $this->assertSame(0, $pattern->getLeaveDaysForDate(
      new DateTime('2016-08-15'), $start
    ));
  }

  public function testGetDefault() {
    $defaultWorkPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);
    WorkPatternFabricator::fabricate();
    WorkPatternFabricator::fabricate();

    $fetchedDefaultWorkPattern = WorkPattern::getDefault();
    $this->assertInstanceOf(WorkPattern::class, $fetchedDefaultWorkPattern);
    $this->assertEquals($defaultWorkPattern->id, $fetchedDefaultWorkPattern->id);
  }

  private function updateBasicWorkPattern($id, $params) {
    $params['id'] = $id;
    return WorkPatternFabricator::fabricate($params);
  }

  public function testGetWorkDayTypeForDateShouldHaveCorrectDayTypeForPatternsWithOnlyOneWeek() {
    $pattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();

    $start = new DateTime('2016-01-01');

    // A friday
    $this->assertEquals(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-01-01'), $start
    ));

    // A saturday
    $this->assertEquals(WorkDay::WORK_DAY_OPTION_WEEKEND, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-02-13'), $start
    ));

    // A sunday
    $this->assertEquals(WorkDay::WORK_DAY_OPTION_WEEKEND, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-03-06'), $start
    ));

    // A monday
    $this->assertEquals(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-04-04'), $start
    ));

    // A tuesday
    $this->assertEquals(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-05-24'), $start
    ));

    // A wednesday
    $this->assertEquals(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-06-15'), $start
    ));

    // A thursday
    $this->assertEquals(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-07-28'), $start
    ));
  }

  public function testGetWorkDayTypeForDateShouldHaveCorrectDayTypeForPatternsWithMultipleWeeks() {
    // Week 1 weekdays: monday, wednesday and friday
    // Week 2 weekdays: tuesday and thursday
    $pattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();

    $start = new DateTime('2016-07-31'); // A sunday

    // Since the start date is a sunday, the end of the week, the following day
    // (2016-08-01) should be on the second week. Monday of the second week is
    // not a working day
    $this->assertSame(WorkDay::WORK_DAY_OPTION_NO, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-01'), $start
    ));

    // The next day is a tuesday, which is a working day on the second week, so
    $this->assertSame(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-02'), $start
    ));

    // Wednesday is not a working day on the second week
    $this->assertSame(WorkDay::WORK_DAY_OPTION_NO, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-03'), $start
    ));

    // Thursday is a working day on the second week
    $this->assertSame(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-04'), $start
    ));

    // Friday, Saturday and Sunday are not working days on the second week,
    $this->assertSame(WorkDay::WORK_DAY_OPTION_NO, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-05'), $start
    ));
    $this->assertSame(WorkDay::WORK_DAY_OPTION_WEEKEND, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-06'), $start
    ));
    $this->assertSame(WorkDay::WORK_DAY_OPTION_WEEKEND, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-07'), $start
    ));

    // Now, since we hit sunday, the following day will be on the third week
    // since the start date, but the work pattern only has 2 weeks, so we
    // rotate back to use the week 1 from the pattern

    // Monday is a working day on the first week
    $this->assertSame(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-08'), $start
    ));

    // Tuesday is not a working day on the first week
    $this->assertSame(WorkDay::WORK_DAY_OPTION_NO, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-09'), $start
    ));

    // Wednesday is a working day on the first week
    $this->assertSame(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-10'), $start
    ));

    // Thursday is not a working day on the first week
    $this->assertSame(WorkDay::WORK_DAY_OPTION_NO, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-11'), $start
    ));

    // Friday is a working day on the first week
    $this->assertSame(WorkDay::WORK_DAY_OPTION_YES, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-12'), $start
    ));

    // Saturday and Sunday are not working days on the first week
    $this->assertSame(WorkDay::WORK_DAY_OPTION_WEEKEND, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-13'), $start
    ));
    $this->assertSame(WorkDay::WORK_DAY_OPTION_WEEKEND, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-14'), $start
    ));

    // Hit sunday again, so we are now on the fourth week since the start date.
    // The work pattern will rotate and use the week 2

    // Monday is not a working day on week 2
    $this->assertSame(WorkDay::WORK_DAY_OPTION_NO, $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-15'), $start
    ));
  }
  public function testGetWorkDayType() {
    //create a contact with valid contract and a Work pattern assigned
    $contact = ContactFabricator::fabricate();
    $periodStartDate = date('2016-01-01');

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
    ]);
    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id
    ]);
    $startDateTime = new DateTime('2016-11-30');

    $type = WorKday::WORK_DAY_OPTION_YES;
    $dayType = WorkPattern::getWorkDayType($contact['id'], $startDateTime);
    $this->assertEquals($type, $dayType);
  }
}
