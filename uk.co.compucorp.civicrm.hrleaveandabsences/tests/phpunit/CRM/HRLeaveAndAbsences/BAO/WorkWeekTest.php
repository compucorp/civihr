<?php

use CRM_HRLeaveAndAbsences_BAO_WorkWeek as WorkWeek;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_WorkWeekTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_WorkWeekTest extends BaseHeadlessTest {
  protected $workPattern = null;

  public function setUp() {
    $this->workPattern = WorkPatternFabricator::fabricate();
  }

  public function testNumberShouldAlwaysBeMaxNumberPlus1OnCreate() {
    $params = ['pattern_id' => $this->workPattern->id];

    $entity = $this->createWorkWeek($params);
    $this->assertEquals(1, $entity->number);

    $entity2 = $this->createWorkWeek($params);
    $this->assertEquals(2, $entity2->number);
  }

  public function testCannotSetWeekNumberOnCreate() {
    $params = [
        'pattern_id' => $this->workPattern->id,
        'number' => rand(2, 1000)
    ];
    $entity = $this->createWorkWeek($params);
    $this->assertEquals(1, $entity->number);
  }

  public function testCannotChangeWeekNumberOnUpdate() {
    $entity = $this->createWorkWeek([
      'pattern_id' => $this->workPattern->id
    ]);
    $this->assertEquals(1, $entity->number);

    $updatedEntity = $this->updateWorkWeek($entity->id, [
      'number' => rand(100, 200)
    ]);
    $this->assertEquals($entity->number, $updatedEntity->number);
  }

  public function testCannotChangeWorkPatternId() {
    $entity = $this->createWorkWeek([
      'pattern_id' => $this->workPattern->id
    ]);
    $this->assertEquals($this->workPattern->id, $entity->pattern_id);

    $updatedEntity = $this->updateWorkWeek($entity->id, [
      'pattern_id' => rand(100, 200)
    ]);
    $this->assertEquals($this->workPattern->id, $updatedEntity->pattern_id);
  }

  public function testCanCreateWorkWeekWithDays()
  {
    $params = [
      'pattern_id' => $this->workPattern->id,
      'days' => [
        ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 1],
        ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 2],
        ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 3],
        ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 4],
        ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 5],
        ['type' => 3, 'day_of_the_week' => 6],
        ['type' => 3, 'day_of_the_week' => 7],
      ]
    ];

    $workWeek = $this->createWorkWeek($params);
    $this->assertNotEmpty($workWeek->id);
    $weekDays = $this->getWorkDaysForWeek($workWeek->id);
    $this->assertCount(7, $weekDays);
    foreach($params['days'] as $i => $day) {
      $this->assertEquals($day['type'], $weekDays[$i]->type);
      $this->assertEquals($day['day_of_the_week'], $weekDays[$i]->day_of_the_week);
      if($day['type'] == 2) {
        $this->assertEquals($day['time_from'], $weekDays[$i]->time_from);
        $this->assertEquals($day['time_to'], $weekDays[$i]->time_to);
        $this->assertEquals($day['break'], $weekDays[$i]->break);
        $this->assertEquals($day['leave_days'], $weekDays[$i]->leave_days);
      }
    }
  }

  public function testCanUpdateWorkWeekWithDays()
  {
    $params = ['pattern_id' => $this->workPattern->id];
    $workWeek = $this->createWorkWeek($params);
    $this->assertNotEmpty($workWeek->id);
    $weekDays = $this->getWorkDaysForWeek($workWeek->id);
    $this->assertCount(0, $weekDays);

    $params = [
      'days' => [
        ['type' => 2, 'time_from' => '13:00', 'time_to' => '15:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 1],
        ['type' => 2, 'time_from' => '09:00', 'time_to' => '18:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 2],
        ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 3],
        ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 4],
        ['type' => 2, 'time_from' => '10:00', 'time_to' => '19:00', 'break' => 1, 'leave_days' => 1, 'day_of_the_week' => 5],
        ['type' => 3, 'day_of_the_week' => 6],
        ['type' => 3, 'day_of_the_week' => 7],
      ]
    ];

    $workWeek = $this->updateWorkWeek($workWeek->id, $params);
    $weekDays = $this->getWorkDaysForWeek($workWeek->id);
    $this->assertCount(7, $weekDays);

    foreach($params['days'] as $i => $day) {
      $this->assertEquals($day['type'], $weekDays[$i]->type);
      $this->assertEquals($day['day_of_the_week'], $weekDays[$i]->day_of_the_week);
      if($day['type'] == 2) {
        $this->assertEquals($day['time_from'], $weekDays[$i]->time_from);
        $this->assertEquals($day['time_to'], $weekDays[$i]->time_to);
        $this->assertEquals($day['break'], $weekDays[$i]->break);
        $this->assertEquals($day['leave_days'], $weekDays[$i]->leave_days);
      }
    }
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkWeekException
   * @expectedExceptionMessage A Work Week must contain EXACTLY 7 days
   */
  public function testCannotCreateWorkWeekWithLessThanSevenDays()
  {
    $params = [
      'pattern_id' => $this->workPattern->id,
      'days' => [
        ['type' => 3, 'day_of_the_week' => 1],
        ['type' => 3, 'day_of_the_week' => 2],
      ]
    ];

    $this->createWorkWeek($params);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidWorkWeekException
   * @expectedExceptionMessage A Work Week must contain EXACTLY 7 days
   */
  public function testCannotCreateWorkWeekWithMoreThanSevenDays() {
    $params = [
      'pattern_id' => $this->workPattern->id,
      'days' => [
        ['type' => 3, 'day_of_the_week' => 1],
        ['type' => 3, 'day_of_the_week' => 2],
        ['type' => 3, 'day_of_the_week' => 3],
        ['type' => 3, 'day_of_the_week' => 4],
        ['type' => 3, 'day_of_the_week' => 5],
        ['type' => 3, 'day_of_the_week' => 6],
        ['type' => 3, 'day_of_the_week' => 7],
        ['type' => 3, 'day_of_the_week' => 7],
      ]
    ];

    $this->createWorkWeek($params);
  }

  private function createWorkWeek($params) {
    return WorkWeek::create($params);
  }

  private function updateWorkWeek($id, $params) {
    $params['id'] = $id;
    CRM_HRLeaveAndAbsences_BAO_WorkWeek::create($params);

    return WorkWeek::findById($id);
  }

  private function getWorkDaysForWeek($weekId) {
    $workDays = [];

    $workDay = new WorkDay();
    $workDay->week_id = $weekId;
    $workDay->find();

    while($workDay->fetch()) {
      $workDays[] = clone $workDay;
    }

    return $workDays;
  }
}
