<?php

require_once __DIR__."/../BaseTest.php";

use CRM_HRLeaveAndAbsences_BaseTest as BaseTest;
use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_PublicHolidayTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_PublicHolidayTest extends BaseTest {

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   * @expectedExceptionMessage Date value is required
   */
  public function testPublicHolidayDateShouldNotBeEmpty() {
    PublicHoliday::create([
      'title' => 'Public holiday 1',
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   * @expectedExceptionMessage Date value should be valid
   */
  public function testPublicHolidayDateShouldBeValid() {
    PublicHoliday::create([
      'title' => 'Public holiday 1',
      'date' => '2016-06-01',
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   * @expectedExceptionMessage There is a Public Holiday already existing with given date
   */
  public function testPublicHolidayDateShouldBeUnique() {
    PublicHoliday::create([
      'title' => 'Public holiday 1',
      'date' => CRM_Utils_Date::processDate('2016-06-01'),
    ]);
    PublicHoliday::create([
      'title' => 'Public holiday 2',
      'date' => CRM_Utils_Date::processDate('2016-06-01'),
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   * @expectedExceptionMessage Title value is required
   */
  public function testPublicHolidayTitleShouldNotBeEmpty() {
    PublicHoliday::create([
      'date' => CRM_Utils_Date::processDate('2016-07-01'),
    ]);
  }

  public function testGetNumberOfPublicHolidaysForPeriod()
  {
    $this->createBasicPublicHoliday(['date' => CRM_Utils_Date::processDate('2016-01-01')]);
    $this->createBasicPublicHoliday(['date' => CRM_Utils_Date::processDate('2016-03-25')]);
    $this->createBasicPublicHoliday(['date' => CRM_Utils_Date::processDate('2016-05-02')]);
    $this->createBasicPublicHoliday(['date' => CRM_Utils_Date::processDate('2016-05-30')]);
    $this->createBasicPublicHoliday(['date' => CRM_Utils_Date::processDate('2016-08-29')]);
    $this->createBasicPublicHoliday(['date' => CRM_Utils_Date::processDate('2016-12-25')]);
    $this->createBasicPublicHoliday(['date' => CRM_Utils_Date::processDate('2016-12-26')]);
    $this->createBasicPublicHoliday(['date' => CRM_Utils_Date::processDate('2016-12-27')]);

    $this->assertEquals(
      8,
      PublicHoliday::getNumberOfPublicHolidaysForPeriod('2016-01-01', '2016-12-31')
    );

    $this->assertEquals(
      1,
      PublicHoliday::getNumberOfPublicHolidaysForPeriod('2016-01-01', '2016-01-31')
    );

    $this->assertEquals(
      0,
      PublicHoliday::getNumberOfPublicHolidaysForPeriod('2016-02-01', '2016-02-29')
    );

    $this->assertEquals(
      1,
      PublicHoliday::getNumberOfPublicHolidaysForPeriod('2016-02-02', '2016-03-31')
    );

    $this->assertEquals(
      3,
      PublicHoliday::getNumberOfPublicHolidaysForPeriod('2016-04-01', '2016-08-30')
    );

    $this->assertEquals(
      3,
      PublicHoliday::getNumberOfPublicHolidaysForPeriod('2016-08-30', '2016-12-28')
    );
  }

  public function testGetNumberOfPublicHolidaysDoesntCountNonActiveHolidays()
  {
    $this->createBasicPublicHoliday([
      'date' => CRM_Utils_Date::processDate('2016-02-01')
    ]);
    $this->createBasicPublicHoliday([
      'date'      => CRM_Utils_Date::processDate('2016-07-25'),
      'is_active' => FALSE
    ]);
    $this->createBasicPublicHoliday([
      'date' => CRM_Utils_Date::processDate('2016-04-02')
    ]);

    $this->assertEquals(
      2,
      PublicHoliday::getNumberOfPublicHolidaysForPeriod('2016-02-01', '2016-12-31')
    );
  }

  public function testGetNumberOfPublicHolidaysCanExcludeWeekendsFromCount()
  {
    $this->createBasicPublicHoliday([
      'date' => CRM_Utils_Date::processDate('2016-02-01')
    ]);
    $this->createBasicPublicHoliday([
      'date' => CRM_Utils_Date::processDate('2016-06-04') // Saturday
    ]);
    $this->createBasicPublicHoliday([
      'date' => CRM_Utils_Date::processDate('2016-04-13')
    ]);
    $this->createBasicPublicHoliday([
      'date' => CRM_Utils_Date::processDate('2016-05-15') // Sunday
    ]);

    $this->assertEquals(
      2,
      PublicHoliday::getNumberOfPublicHolidaysForPeriod('2016-02-01', '2016-12-31', true)
    );
  }

  public function testGetNumberOfPublicHolidaysForCurrentPeriod()
  {
    CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::create([
      'title' => 'Current Period',
      'start_date' => date('YmdHis', strtotime('first day of January')),
      'end_date' => date('YmdHis', strtotime('last day of December')),
    ]);

    $this->createBasicPublicHoliday([
      'date' => date('YmdHis', strtotime('2015-01-01'))
    ]);

    $this->createBasicPublicHoliday([
      'date' => date('YmdHis', strtotime('first monday of January'))
    ]);
    $this->createBasicPublicHoliday([
      'date' => date('YmdHis', strtotime('first tuesday of February'))
    ]);
    $this->createBasicPublicHoliday([
      'date' => date('YmdHis', strtotime('last thursday of May'))
    ]);
    $this->createBasicPublicHoliday([
      'date' => date('YmdHis', strtotime('last monday of May'))
    ]);
    $this->createBasicPublicHoliday([
      'date' => date('YmdHis', strtotime('last friday of December'))
    ]);

    $this->assertEquals(
      5,
      PublicHoliday::getNumberOfPublicHolidaysForCurrentPeriod()
    );
  }

  public function testGetNumberOfPublicHolidaysForCurrentPeriodCanExcludeWeekendsFromCount()
  {
    CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::create([
      'title' => 'Current Period',
      'start_date' => date('YmdHis', strtotime('first day of January')),
      'end_date' => date('YmdHis', strtotime('last day of December')),
    ]);

    $this->createBasicPublicHoliday([
      'date' => date('YmdHis', strtotime('first monday of January'))
    ]);
    $this->createBasicPublicHoliday([
      'date' => date('YmdHis', strtotime('first sunday of February'))
    ]);

    $excludeWeekends = true;
    $this->assertEquals(
      1,
      PublicHoliday::getNumberOfPublicHolidaysForCurrentPeriod($excludeWeekends)
    );
  }

  public function testGetPublicHolidaysForPeriod() {
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 1',
      'date' => CRM_Utils_Date::processDate('2016-01-01')
    ]);
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 2',
      'date' => CRM_Utils_Date::processDate('2016-03-25')
    ]);
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 3',
      'date' => CRM_Utils_Date::processDate('2016-12-26')
    ]);
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 4',
      'date' => CRM_Utils_Date::processDate('2016-12-27')
    ]);

    $publicHolidays = PublicHoliday::getPublicHolidaysForPeriod('2016-01-01', '2016-12-31');
    $this->assertCount(4, $publicHolidays);
    $this->assertEquals('Holiday 1', $publicHolidays[0]->title);
    $this->assertEquals('Holiday 2', $publicHolidays[1]->title);
    $this->assertEquals('Holiday 3', $publicHolidays[2]->title);
    $this->assertEquals('Holiday 4', $publicHolidays[3]->title);


    $publicHolidays = PublicHoliday::getPublicHolidaysForPeriod('2016-01-01', '2016-01-31');
    $this->assertCount(1, $publicHolidays);
    $this->assertEquals('Holiday 1', $publicHolidays[0]->title);

    $publicHolidays = PublicHoliday::getPublicHolidaysForPeriod('2016-02-01', '2016-02-29');
    $this->assertCount(0, $publicHolidays);

    $publicHolidays = PublicHoliday::getPublicHolidaysForPeriod('2016-12-01', '2016-12-29');
    $this->assertCount(2, $publicHolidays);
    $this->assertEquals('Holiday 3', $publicHolidays[0]->title);
    $this->assertEquals('Holiday 4', $publicHolidays[1]->title);
  }

  public function testGetPublicHolidaysForPeriodShouldOnlyReturnActivePublicHolidays() {
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 1',
      'date' => CRM_Utils_Date::processDate('2016-01-01'),
      'is_active' => false,
    ]);
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 2',
      'date' => CRM_Utils_Date::processDate('2016-01-02')
    ]);
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 3',
      'date' => CRM_Utils_Date::processDate('2016-01-03')
    ]);

    $publicHolidays = PublicHoliday::getPublicHolidaysForPeriod('2016-01-01', '2016-01-31');
    $this->assertCount(2, $publicHolidays);
    $this->assertEquals('Holiday 2', $publicHolidays[0]->title);
    $this->assertEquals('Holiday 3', $publicHolidays[1]->title);
  }

  public function testGetPublicHolidaysForPeriodCanExcludeWeekends() {
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 1',
      'date' => CRM_Utils_Date::processDate('2016-01-01'),
    ]);
    // 2016-01-02 is a Saturday
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 2',
      'date' => CRM_Utils_Date::processDate('2016-01-02')
    ]);
    // 2016-01-02 is a Sunday
    $this->createBasicPublicHoliday([
      'title' => 'Holiday 3',
      'date' => CRM_Utils_Date::processDate('2016-01-03')
    ]);

    $excludeWeekends = true;
    $publicHolidays = PublicHoliday::getPublicHolidaysForPeriod('2016-01-01', '2016-01-31', $excludeWeekends);
    $this->assertCount(1, $publicHolidays);
    $this->assertEquals('Holiday 1', $publicHolidays[0]->title);
  }

  private function createBasicPublicHoliday($params)
  {
    $basicRequiredFields = [
      'title' => 'Type ' . microtime(),
      'date' => CRM_Utils_Date::processDate(date('Y-m-d')),
    ];

    $params = array_merge($basicRequiredFields, $params);
    return PublicHoliday::create($params);
  }

}
