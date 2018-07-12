<?php

use CRM_HRLeaveAndAbsences_Validator_TimeZone as TimeZoneValidator;
/**
 * Class RM_HRLeaveAndAbsences_Validator_TimeZoneTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Validator_TimeZoneTest extends BaseHeadlessTest {

  /**
   * @dataProvider timeZoneDataProvider
   */
  public function testIsValid($timeZone, $expectedResult) {
    $result = TimeZoneValidator::isValid($timeZone);
    $this->assertEquals($expectedResult, $result);
  }

  public function timeZoneDataProvider() {
    return [
      ['America/New_York', TRUE],
      ['Europe/Bucharest', TRUE],
      ['AnyWhere/Bucharest', FALSE],
      ['Pacific/Fiji', TRUE],
      ['Sample/Time', FALSE],
      ['Asia/Tokyo', TRUE],
      ['Atlantic/Cape_Verde', TRUE],
      ['Atlantic/All_Region', FALSE],
    ];
  }
}
