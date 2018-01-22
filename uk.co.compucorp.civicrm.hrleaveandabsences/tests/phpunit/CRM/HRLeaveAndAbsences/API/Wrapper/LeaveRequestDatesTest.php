<?php

use CRM_HRLeaveAndAbsences_API_Wrapper_LeaveRequestDates as LeaveRequestDates;

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_API_Wrapper_LeaveRequestDatesTest extends BaseHeadlessTest {

  /**
   * @var LeaveRequestDates
   */
  private $wrapper;

  public function setUp() {
    $this->wrapper = new LeaveRequestDates();
  }

  public function testRequestParamsAreNotUpdatedIfTheRequestIsNotForLeaveRequestGetOrGetFull() {
    $apiRequest = [
      'entity' => 'LeaveRequest',
      //some random action name, so that we have different actions for different tests
      'action' => 'get' . rand(10, 1000),
      'params' => [
        'from_date' => '2016-01-01',
        'to_date' => '2016-01-01'
      ]
    ];

    $wrappedRequest = $this->wrapper->fromApiInput($apiRequest);

    $this->assertEquals($apiRequest, $wrappedRequest);
  }

  /**
   * @dataProvider supportedActions
   */
  public function testToAndFromDateHoursAreSetIfTheGivenDatesDontHaveHour($action) {
    $apiRequest = [
      'entity' => 'LeaveRequest',
      'action' => $action,
      'params' => [
        'from_date' => '2016-02-01',
        'to_date' => '2016-02-03'
      ]
    ];

    $wrappedRequest = $this->wrapper->fromApiInput($apiRequest);

    $this->assertEquals('2016-02-01 00:00:00', $wrappedRequest['params']['from_date']);
    $this->assertEquals('2016-02-03 23:59:59', $wrappedRequest['params']['to_date']);
  }

  /**
   * @dataProvider supportedActions
   */
  public function testToAndFromDateHoursAreSetIfTheGivenDatesDontHaveHourAndTheParamsHaveOperators($action) {
    $apiRequest = [
      'entity' => 'LeaveRequest',
      'action' => $action,
      'params' => [
        'from_date' => ['>' => '2016-02-01'],
        'to_date' => ['<=' => '2016-02-03']
      ]
    ];

    $wrappedRequest = $this->wrapper->fromApiInput($apiRequest);

    $this->assertEquals(['>' => '2016-02-01 00:00:00'], $wrappedRequest['params']['from_date']);
    $this->assertEquals(['<=' => '2016-02-03 23:59:59'], $wrappedRequest['params']['to_date']);
  }

  /**
   * @dataProvider supportedActions
   */
  public function testToAndFromDateHoursAreNotTouchedIfTheGivenDatesAlreadyHaveHours($action) {
    $apiRequest = [
      'entity' => 'LeaveRequest',
      'action' => $action,
      'params' => [
        'from_date' => '2016-02-01 11:53:00',
        'to_date' => '2016-02-03 17:15:10'
      ]
    ];

    $wrappedRequest = $this->wrapper->fromApiInput($apiRequest);

    $this->assertEquals('2016-02-01 11:53:00', $wrappedRequest['params']['from_date']);
    $this->assertEquals('2016-02-03 17:15:10', $wrappedRequest['params']['to_date']);
  }

  public function testTheOutputIsNeverChanged() {
    $result = [
      'test' => '1234'
    ];

    $wrappedResult = $this->wrapper->toApiOutput([], $result);

    $this->assertEquals($result, $wrappedResult);
  }

  public function supportedActions() {
    return [
      ['get'],
      ['getfull']
    ];
  }
}
