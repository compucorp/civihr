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

  /**
   * @dataProvider sampleLeaveDataProvider
   */
  public function testAdjustModifiesTheLeaveTimeCorrectlyForDayTypeRequests($leaveRequest) {
    $expectedFromDateTime = $leaveRequest['from_time_expected'];
    $expectedToDateTime = $leaveRequest['to_time_expected'];

    //Adjust leave time.
    CalendarLeaveTimeHelper::adjust($leaveRequest);
    $this->assertEquals($expectedFromDateTime, $leaveRequest['from_date']);
    $this->assertEquals($expectedToDateTime, $leaveRequest['to_date']);
  }

  public function testAdjustDoesNotModifyTheLeaveTimeForNonDayTypeRequests() {
    //Non Day type requests will have the from_date_type and to_date_type parameter
    //empty
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

  private function getLeaveDayTypes() {
    if (!$this->leaveDayTypes) {
      $this->leaveDayTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));
    }

    return $this->leaveDayTypes;
  }

  public function sampleLeaveDataProvider() {
    $leaveDayTypes = $this->getLeaveDayTypes();

    return [
      [
        [
          //Same day request
          'from_date' => '2018-06-01 13:00',
          'to_date' => '2018-06-01 14:00',
          'from_date_type' => $leaveDayTypes['all_day'],
          'to_date_type' => $leaveDayTypes['all_day'],
          'from_time_expected' => '2018-06-01 06:00:00',
          'to_time_expected' => '2018-06-01 19:00:00'
        ]
      ],
      [
        [  // Same day request
          'from_date' => '2018-06-01 13:00',
          'to_date' => '2018-06-01 14:00',
          'from_date_type' => $leaveDayTypes['half_day_am'],
          'to_date_type' => $leaveDayTypes['half_day_am'],
          'from_time_expected' => '2018-06-01 06:00:00',
          'to_time_expected' => '2018-06-01 12:00:00'
        ]
      ],
      [
        [ // Same day request
          'from_date' => '2018-06-01 13:00',
          'to_date' => '2018-06-01 14:00',
          'from_date_type' => $leaveDayTypes['half_day_pm'],
          'to_date_type' => $leaveDayTypes['half_day_pm'],
          'from_time_expected' => '2018-06-01 12:01:00',
          'to_time_expected' => '2018-06-01 19:00:00'
        ]
      ],
      [
        [
          'from_date' => '2018-06-01 13:00',
          'to_date' => '2018-06-02 15:00',
          'from_date_type' => $leaveDayTypes['half_day_am'],
          'to_date_type' => $leaveDayTypes['half_day_am'],
          'from_time_expected' => '2018-06-01 06:00:00',
          'to_time_expected' => '2018-06-02 12:00:00'
        ]
      ],
      [
        [
          'from_date' => '2018-06-01 13:00',
          'to_date' => '2018-06-02 15:00',
          'from_date_type' => $leaveDayTypes['half_day_pm'],
          'to_date_type' => $leaveDayTypes['half_day_pm'],
          'from_time_expected' => '2018-06-01 12:01:00',
          'to_time_expected' => '2018-06-02 19:00:00'
        ]
      ],
      [
       [
         'from_date' => '2018-06-01 13:00',
         'to_date' => '2018-06-02 15:00',
         'from_date_type' => $leaveDayTypes['half_day_am'],
         'to_date_type' => $leaveDayTypes['half_day_pm'],
         'from_time_expected' => '2018-06-01 06:00:00',
         'to_time_expected' => '2018-06-02 19:00:00'
       ]
      ],
      [
        [
          'from_date' => '2018-06-01 13:00',
          'to_date' => '2018-06-02 15:00',
          'from_date_type' => $leaveDayTypes['half_day_pm'],
          'to_date_type' => $leaveDayTypes['half_day_am'],
          'from_time_expected' => '2018-06-01 12:01:00',
          'to_time_expected' => '2018-06-02 12:00:00'
        ]
      ],
      [
       [
         'from_date' => '2018-06-01 13:00',
         'to_date' => '2018-06-02 15:00',
         'from_date_type' => $leaveDayTypes['all_day'],
         'to_date_type' => $leaveDayTypes['half_day_am'],
         'from_time_expected' => '2018-06-01 06:00:00',
         'to_time_expected' => '2018-06-02 12:00:00'
       ]
      ],
      [
       [
         'from_date' => '2018-06-01 13:00',
         'to_date' => '2018-06-02 15:00',
         'from_date_type' => $leaveDayTypes['all_day'],
         'to_date_type' => $leaveDayTypes['half_day_pm'],
         'from_time_expected' => '2018-06-01 06:00:00',
         'to_time_expected' => '2018-06-02 19:00:00'
       ]
      ],
      [
        [
          'from_date' => '2018-06-01 13:00',
          'to_date' => '2018-06-02 15:00',
          'from_date_type' => $leaveDayTypes['half_day_am'],
          'to_date_type' => $leaveDayTypes['all_day'],
          'from_time_expected' => '2018-06-01 06:00:00',
          'to_time_expected' => '2018-06-02 19:00:00'
        ]
      ],
      [
       [
         'from_date' => '2018-06-01 13:00',
         'to_date' => '2018-06-02 15:00',
         'from_date_type' => $leaveDayTypes['half_day_pm'],
         'to_date_type' => $leaveDayTypes['all_day'],
         'from_time_expected' => '2018-06-01 12:01:00',
         'to_time_expected' => '2018-06-02 19:00:00'
       ]
      ],
      [
       [
         'from_date' => '2018-06-01 13:00',
         'to_date' => '2018-06-02 15:00',
         'from_date_type' => $leaveDayTypes['all_day'],
         'to_date_type' => $leaveDayTypes['all_day'],
         'from_time_expected' => '2018-06-01 06:00:00',
         'to_time_expected' => '2018-06-02 19:00:00'
       ]
      ],
    ];
  }
}
