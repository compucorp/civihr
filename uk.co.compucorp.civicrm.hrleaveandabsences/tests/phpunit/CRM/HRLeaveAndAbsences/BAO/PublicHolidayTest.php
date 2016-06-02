<?php

use Civi\Test\HeadlessInterface;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_PublicHolidayTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_PublicHolidayTest extends CiviUnitTestCase implements HeadlessInterface {

  protected $_tablesToTruncate = [
    'civicrm_hrleaveandabsences_public_holiday',
  ];

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  public function setUp() {
    parent::setUp();
  }

  public function tearDown() {
    parent::tearDown();
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

}
