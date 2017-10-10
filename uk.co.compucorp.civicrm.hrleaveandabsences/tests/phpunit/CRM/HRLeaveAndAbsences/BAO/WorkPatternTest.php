<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_HRLeaveAndAbsences_Queue_PublicHolidayLeaveRequestUpdates as PublicHolidayLeaveRequestUpdatesQueue;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_WorkPatternTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_WorkPatternTest extends BaseHeadlessTest {

  private $workDayTypeOptions;

  public function setUp() {
    $this->workDayTypeOptions = array_flip(WorkDay::buildOptions('type', 'validate'));
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
    $this->assertEquals(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-01-01'), $start
    ));

    // A saturday
    $this->assertEquals(WorkDay::getWeekendTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-02-13'), $start
    ));

    // A sunday
    $this->assertEquals(WorkDay::getWeekendTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-03-06'), $start
    ));

    // A monday
    $this->assertEquals(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-04-04'), $start
    ));

    // A tuesday
    $this->assertEquals(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-05-24'), $start
    ));

    // A wednesday
    $this->assertEquals(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-06-15'), $start
    ));

    // A thursday
    $this->assertEquals(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
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
    $this->assertSame(WorkDay::getNonWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-01'), $start
    ));

    // The next day is a tuesday, which is a working day on the second week, so
    $this->assertSame(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-02'), $start
    ));

    // Wednesday is not a working day on the second week
    $this->assertSame(WorkDay::getNonWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-03'), $start
    ));

    // Thursday is a working day on the second week
    $this->assertSame(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-04'), $start
    ));

    // Friday, Saturday and Sunday are not working days on the second week,
    $this->assertSame(WorkDay::getNonWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-05'), $start
    ));
    $this->assertSame(WorkDay::getWeekendTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-06'), $start
    ));
    $this->assertSame(WorkDay::getWeekendTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-07'), $start
    ));

    // Now, since we hit sunday, the following day will be on the third week
    // since the start date, but the work pattern only has 2 weeks, so we
    // rotate back to use the week 1 from the pattern

    // Monday is a working day on the first week
    $this->assertSame(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-08'), $start
    ));

    // Tuesday is not a working day on the first week
    $this->assertSame(WorkDay::getNonWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-09'), $start
    ));

    // Wednesday is a working day on the first week
    $this->assertSame(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-10'), $start
    ));

    // Thursday is not a working day on the first week
    $this->assertSame(WorkDay::getNonWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-11'), $start
    ));

    // Friday is a working day on the first week
    $this->assertSame(WorkDay::getWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-12'), $start
    ));

    // Saturday and Sunday are not working days on the first week
    $this->assertSame(WorkDay::getWeekendTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-13'), $start
    ));
    $this->assertSame(WorkDay::getWeekendTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-14'), $start
    ));

    // Hit sunday again, so we are now on the fourth week since the start date.
    // The work pattern will rotate and use the week 2

    // Monday is not a working day on week 2
    $this->assertSame(WorkDay::getNonWorkingDayTypeValue(), $pattern->getWorkDayTypeForDate(
      new DateTime('2016-08-15'), $start
    ));
  }

  public function testGetCalendarCanGenerateTheCalendarForAWorkPatternWithASingleWeek() {
    $workDayTypes = $this->workDayTypeOptions;
    $expectedCalendar = [
      [
        'date' => '2016-01-01', // friday
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-02', // saturday
        'type' => $workDayTypes['weekend']
      ],
      [
        'date' => '2016-01-03', // sunday
        'type' => $workDayTypes['weekend']
      ],
      [
        'date' => '2016-01-04', // monday
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-05', // tuesday
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-06', // wednesday
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-07', // thursday
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-08', // friday
        'type' => $workDayTypes['working_day']
      ],
    ];

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    $calendar = $workPattern->getCalendar(
      new DateTime('2016-01-01'),
      new DateTime('2016-01-01'),
      new DateTime('2016-01-08')
    );

    $this->assertEquals($expectedCalendar, $calendar);
  }

  public function testGetCalendarCanGenerateTheCalendarForAWorkPatternWithMultipleWeeks() {
    $workDayTypes = $this->workDayTypeOptions;
    $expectedCalendar = [
      [
        'date' => '2016-01-01', // friday, working day on first week
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-02', // saturday
        'type' => $workDayTypes['weekend']
      ],
      [
        'date' => '2016-01-03', // sunday
        'type' => $workDayTypes['weekend']
      ],
      [
        'date' => '2016-01-04', // monday, non working day on second week
        'type' => $workDayTypes['non_working_day']
      ],
      [
        'date' => '2016-01-05', // tuesday
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-06', // wednesday, non working day on second week
        'type' => $workDayTypes['non_working_day']
      ],
      [
        'date' => '2016-01-07', // thursday
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-08', // friday, non working day on second week
        'type' => $workDayTypes['non_working_day']
      ],
      [
        'date' => '2016-01-09', // saturday
        'type' => $workDayTypes['weekend']
      ],
      [
        'date' => '2016-01-10', // sunday
        'type' => $workDayTypes['weekend']
      ],
      [
        'date' => '2016-01-11', // monday, working day on first week (looped back to the first week)
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-12', // tuesday, non working day on first week
        'type' => $workDayTypes['non_working_day']
      ],
      [
        'date' => '2016-01-13', // wednesday, working day on first week
        'type' => $workDayTypes['working_day']
      ],
      [
        'date' => '2016-01-14', // thursday, non working day on first week
        'type' => $workDayTypes['non_working_day']
      ],
      [
        'date' => '2016-01-15', // friday, working day on first week
        'type' => $workDayTypes['working_day']
      ],
    ];

    $workPattern = WorkPatternFabricator::fabricateWithTwoWeeksAnd31AndHalfHours();
    $calendar = $workPattern->getCalendar(
      new DateTime('2016-01-01'),
      new DateTime('2016-01-01'),
      new DateTime('2016-01-15')
    );

    $this->assertEquals($expectedCalendar, $calendar);
  }

  public function testItDoesNotEnqueueTaskToUpdatePublicHolidayLeaveRequestsWhenANewWorkPatternIsCreatedAndNotSetToDefault() {
    WorkPatternFabricator::fabricate(['is_default' => 0]);
    $numberOfItems = 0;
    $this->assertPublicHolidayQueueTask($numberOfItems);
  }

  public function testItEnqueueTaskToUpdatePublicHolidayLeaveRequestsWhenANewWorkPatternIsCreatedSetToDefault() {
    $workPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);
    $this->assertPublicHolidayQueueTask(
      1,
      'CRM_HRLeaveAndAbsences_Queue_Task_UpdateAllFuturePublicHolidayLeaveRequestsForWorkPatternContacts',
      $workPattern->id
    );
  }

  public function testItEnqueueTaskToUpdatePublicHolidayLeaveRequestsWhenTheDefaultWorkPatternIsUpdated() {
    $workPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);
    $this->assertPublicHolidayQueueTask(
      1,
      'CRM_HRLeaveAndAbsences_Queue_Task_UpdateAllFuturePublicHolidayLeaveRequestsForWorkPatternContacts',
      $workPattern->id
    );

    //update work pattern
    WorkPatternFabricator::fabricate(['id' => $workPattern->id, ]);

    $this->assertPublicHolidayQueueTask(
      1,
      'CRM_HRLeaveAndAbsences_Queue_Task_UpdateAllFuturePublicHolidayLeaveRequestsForWorkPatternContacts',
      $workPattern->id
    );
  }

  public function testItEnqueueTaskToUpdatePublicHolidayLeaveRequestsWhenANonDefaultWorkPatternIsUpdated() {
    $workPattern = WorkPatternFabricator::fabricate();
    $numberOfItems = 0;
    $this->assertPublicHolidayQueueTask($numberOfItems);

    //update work pattern
    WorkPatternFabricator::fabricate(['id' => $workPattern->id, ]);

    $this->assertPublicHolidayQueueTask(
      1,
      'CRM_HRLeaveAndAbsences_Queue_Task_UpdateAllFuturePublicHolidayLeaveRequestsForWorkPatternContacts',
      $workPattern->id
    );
  }

  private function assertPublicHolidayQueueTask($numberOfItems, $class = null, $expectedArgument = null) {
    $queue = PublicHolidayLeaveRequestUpdatesQueue::getQueue();
    $this->assertEquals($numberOfItems, $queue->numberOfItems());
    $item = '';

    if($class || $expectedArgument) {
      $item = $queue->claimItem();
    }

    if($class) {
      $this->assertEquals($class, $item->data->callback[0]);
    }

    if($expectedArgument) {
      $this->assertEquals($expectedArgument, $item->data->arguments[0]);
    }

    if($item) {
      $queue->deleteItem($item);
    }
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkPatternException
   * @expectedExceptionMessage You cannot create a new Work Pattern as default and set disabled
   */
  public function testCannotCreateANewDefaultWorkPatternThatIsInactive() {
    WorkPatternFabricator::fabricate(['is_default' => 1, 'is_active' => 0]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkPatternException
   * @expectedExceptionMessage You cannot disable the default Work Pattern
   */
  public function testCannotDisableTheDefaultWorkPattern() {
    $workPattern = WorkPatternFabricator::fabricate(['is_default' => 1]);
    $params = ['id' => $workPattern->id, 'is_active' => 0];

    WorkPattern::create($params);
  }

  public function testWorkPatternLabelsShouldBeUnique() {
    WorkPatternFabricator::fabricate(['label' => 'WorkPattern 1']);

    $this->setExpectedException(
      'CRM_HRLeaveAndAbsences_Exception_InvalidWorkPatternException',
      'Work Pattern with same label already exists!'
    );
    WorkPatternFabricator::fabricate(['label' => 'WorkPattern 1']);
  }

  public function testNoExceptionIsThrownWhenUpdatingAWorkPatternWithoutChangingTheLabel() {
    $params = ['label' => 'WorkPattern 1'];
    $workPattern = WorkPatternFabricator::fabricate($params);

    //update the work pattern
    $params['id'] = $workPattern->id;
    $params['description'] = 'This is a cool Work Pattern';

    try{
      $workPattern = WorkPatternFabricator::fabricate($params);
      $this->assertEquals($workPattern->description, $params['description']);
    } catch(CRM_HRLeaveAndAbsences_Exception_InvalidWorkPatternException $e) {
      $this->fail($e->getMessage());
    }
  }

  public function testExceptionIsThrownWhenUpdatingAWorkPatternWithLabelOfAnotherExistingWorkPattern() {
    $params1 = ['label' => 'WorkPattern 1'];
    $workPattern1 = WorkPatternFabricator::fabricate($params1);

    $params2 = ['label' => 'WorkPattern 2'];
    $workPattern2 = WorkPatternFabricator::fabricate($params2);

    //update the second work pattern with the label of the first pattern
    $params['id'] = $workPattern2->id;
    $params['label'] = $params1['label'];

    $this->setExpectedException(
      CRM_HRLeaveAndAbsences_Exception_InvalidWorkPatternException::class,
      'Work Pattern with same label already exists!'
    );

    WorkPatternFabricator::fabricate($params);
  }
}
