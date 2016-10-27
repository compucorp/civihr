<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

/**
 * Class api_v3_PublicHolidayTest
 *
 * @group headless
 */
class api_v3_PublicHolidayTest extends BaseHeadlessTest {

  public function testGetCountForCurrentPeriod() {
    AbsencePeriod::create([
      'title' => 'Current Period',
      'start_date' => date('YmdHis', strtotime('first day of January')),
      'end_date' => date('YmdHis', strtotime('last day of December')),
    ]);

    $result = civicrm_api3('PublicHoliday', 'getcountforcurrentperiod');
    $this->assertEquals(0, $result);

    PublicHoliday::create([
      'title' => 'Public Holiday 1',
      'date' => date('YmdHis', strtotime('first monday of January'))
    ]);

    $result = civicrm_api3('PublicHoliday', 'getcountforcurrentperiod');
    $this->assertEquals(1, $result);

    PublicHoliday::create([
      'title' => 'Public Holiday 2',
      'date' => date('YmdHis', strtotime('first tuesday of February'))
    ]);

    $result = civicrm_api3('PublicHoliday', 'getcountforcurrentperiod');
    $this->assertEquals(2, $result);
  }

  public function testGetCountForCurrentPeriodCanExcludeWeekends() {
    AbsencePeriod::create([
      'title' => 'Current Period',
      'start_date' => date('YmdHis', strtotime('first day of January')),
      'end_date' => date('YmdHis', strtotime('last day of December')),
    ]);

    PublicHoliday::create([
      'title' => 'Public Holiday Weekday',
      'date' => date('YmdHis', strtotime('first monday of January'))
    ]);

    PublicHoliday::create([
      'title' => 'Public Holiday Weekend',
      'date' => date('YmdHis', strtotime('first sunday of February'))
    ]);

    $result = civicrm_api3('PublicHoliday', 'getcountforcurrentperiod', [
      'exclude_weekends' => 1
    ]);
    $this->assertEquals(1, $result);
  }
}
