<?php

use CRM_HRLeaveAndAbsences_Helper_CalendarFeed_LeaveTime as CalendarLeaveTimeHelper;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * Class CRM_HRLeaveAndAbsences_Helper_CalendarFeed_LeaveTimeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Helper_CalendarFeed_LeaveTimeTest extends BaseHeadlessTest {


  private $leaveDayTypes;

  public function testAdjustDoesNotModifyTheLeaveTimeForNonDayTypeRequests() {
    //Non Day type requests will have the from_date_type and to_date_type parameter empty
    $fromDateTime = '2018-06-01 13:00';
    $toDateTime = '2018-06-02 15:00';
    $leaveRequest = [
      'from_date' => $fromDateTime,
      'to_date' => $toDateTime,
      'from_date_type' => '',
      'to_date_type' => '',
    ];

    //Adjust leave time.
    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($fromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($toDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo06AndToTimeTo19HoursWhenFromDateTypeAndToDateTypeIsAllDaySameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-06-01 14:00';
    $leaveRequest = [
      //Same day request
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['all_day'],
      'to_date_type' => $leaveDayTypes['all_day'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '06:00:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '19:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo06AndToTimeTo12HoursWhenFromDateTypeAndToDateTypeIsHalfDayAmSameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-06-01 14:00';
    $leaveRequest = [
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['half_day_am'],
      'to_date_type' => $leaveDayTypes['half_day_am'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '06:00:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '12:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo12HoursOneMinuteAndToTimeTo19HoursWhenFromDateTypeAndToDateTypeIsHalfDayPmSameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-06-01 14:00';
    $leaveRequest = [
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['half_day_pm'],
      'to_date_type' => $leaveDayTypes['half_day_pm'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '12:01:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '19:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo06HoursAndToTimeTo19HoursWhenFromDateTypeIsHalfDayAmAndToDateTypeIsHalfDayPmNotSameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-06-02 14:00';
    $leaveRequest = [
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['half_day_am'],
      'to_date_type' => $leaveDayTypes['half_day_pm'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '06:00:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '19:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo12HoursOneMinuteAndToTimeTo12HoursWhenFromDateTypeIsHalfDayPmAndToDateTypeIsHalfDayAmNotSameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-07-04 14:00';
    $leaveRequest = [
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['half_day_pm'],
      'to_date_type' => $leaveDayTypes['half_day_am'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '12:01:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '12:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo06HoursAndToTimeTo12HoursWhenFromDateTypeIsAllDayAndToDateTypeIsHalfDayAmNotSameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-07-04 14:00';
    $leaveRequest = [
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['all_day'],
      'to_date_type' => $leaveDayTypes['half_day_am'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '06:00:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '12:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo06HoursAndToTimeTo19HoursWhenFromDateTypeIsAllDayAndToDateTypeIsHalfDayPmNotSameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-07-04 14:00';
    $leaveRequest = [
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['all_day'],
      'to_date_type' => $leaveDayTypes['half_day_pm'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '06:00:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '19:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo06HoursAndToTimeTo19HoursWhenFromDateTypeIsHalfDayAmAndToDateTypeIsAllDayNotSameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-06-03 14:00';
    $leaveRequest = [
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['half_day_am'],
      'to_date_type' => $leaveDayTypes['all_day'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '06:00:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '19:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo12HoursPlusOneMinuteAndToTimeTo19HoursWhenFromDateTypeIsHalfDayPmAndToDateTypeIsAllDayNotSameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-07-04 14:00';
    $leaveRequest = [
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['half_day_pm'],
      'to_date_type' => $leaveDayTypes['all_day'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '12:01:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '19:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testLeaveFromTimeIsSetTo06HoursAndToTimeTo19HoursWhenFromDateTypeAndToDateTypeIsAllDayNotSameDay() {
    $leaveDayTypes = $this->getLeaveDayTypes();
    $fromDate = '2018-06-01 13:00';
    $toDate = '2018-07-04 14:00';
    $leaveRequest = [
      'from_date' => $fromDate,
      'to_date' => $toDate,
      'from_date_type' => $leaveDayTypes['all_day'],
      'to_date_type' => $leaveDayTypes['all_day'],
    ];

    $expectedFromDateTime = $this->getAdjustedLeaveTime($fromDate, '06:00:00');
    $expectedToDateTime = $this->getAdjustedLeaveTime($toDate, '19:00:00');

    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  private function getAdjustedLeaveTime($leaveDateTime, $time) {
    return substr($leaveDateTime, 0, 11) . $time;
  }

  private function getLeaveDayTypes() {
    if (!$this->leaveDayTypes) {
      $this->leaveDayTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));
    }

    return $this->leaveDayTypes;
  }
}
