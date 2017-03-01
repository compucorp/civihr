<?php

use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_Test_Fabricator_PublicHoliday as PublicHolidayFabricator;

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

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('first monday of January'))
    ]);

    $result = civicrm_api3('PublicHoliday', 'getcountforcurrentperiod');
    $this->assertEquals(1, $result);

    PublicHolidayFabricator::fabricateWithoutValidation([
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

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('first monday of January'))
    ]);

    PublicHolidayFabricator::fabricateWithoutValidation([
      'date' => date('YmdHis', strtotime('first sunday of February'))
    ]);

    $result = civicrm_api3('PublicHoliday', 'getcountforcurrentperiod', [
      'exclude_weekends' => 1
    ]);
    $this->assertEquals(1, $result);
  }
}
