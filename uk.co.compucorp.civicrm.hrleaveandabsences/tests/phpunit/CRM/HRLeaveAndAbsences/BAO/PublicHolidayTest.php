<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_PublicHolidayTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_PublicHolidayTest extends PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   * @expectedExceptionMessage Date value is required
   */
  public function testPublicHolidayDateShouldNotBeEmpty() {
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public holiday 1',
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   * @expectedExceptionMessage Date value should be valid
   */
  public function testPublicHolidayDateShouldBeValid() {
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public holiday 1',
      'date' => '2016-06-01',
    ]);
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testPublicHolidayDateShouldBeUnique() {
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public holiday 1',
      'date' => CRM_Utils_Date::processDate('2016-06-01'),
    ]);
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public holiday 2',
      'date' => CRM_Utils_Date::processDate('2016-06-01'),
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidPublicHolidayException
   * @expectedExceptionMessage Title value is required
   */
  public function testPublicHolidayTitleShouldNotBeEmpty() {
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'date' => CRM_Utils_Date::processDate('2016-07-01'),
    ]);
  }

}
