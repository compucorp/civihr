<?php

class api_v3_LeaveRequestCalendarFeedConfigTest extends BaseHeadlessTest {

  public function testGetReturnsUnSerializedValuesForFilterFields() {
    $visibleTo = [
      'department' => [1,2],
      'location' => [1]
    ];

    $composedOf = [
      'leave_type' => [1],
      'department' => [3],
      'location' => [3]
    ];

    $results = civicrm_api3('LeaveRequestCalendarFeedConfig', 'create', [
      'title' => 'Feed 1',
      'timezone' => 'America/Monterrey',
      'composed_of' => $composedOf,
      'visible_to' => $visibleTo
    ]);

    $calendarFeedConfig = civicrm_api3('LeaveRequestCalendarFeedConfig', 'get', ['id' => $results['id']]);

    $calendarFeedConfig = array_shift($calendarFeedConfig['values']);
    $this->assertEquals($visibleTo, $calendarFeedConfig['visible_to']);
    $this->assertEquals($composedOf, $calendarFeedConfig['composed_of']);
  }
}
