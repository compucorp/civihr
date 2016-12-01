<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRAbsence_BAO_HRAbsencePeriod as AbsencePeriod;
use CRM_HRAbsence_Test_Fabricator_HRAbsencePeriod as AbsencePeriodFabricator;

/**
 * Class CRM_HRAbsence_BAO_HRAbsencePeriodTest
 *
 * @group headless
 */
class CRM_HRAbsence_BAO_HRAbsencePeriodTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface, TransactionalInterface {

  /**
   * @var array
   *  An array of AbsencePeriods created by the setUp() method
   */
  private $absencePeriods = [];

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  public function setUp() {
    $absencePeriodTable = AbsencePeriod::getTableName();
    // We need to disable fk checks in order to avoid constraint
    // errors when truncating the absence period table
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
    CRM_Core_DAO::executeQuery("TRUNCATE $absencePeriodTable");
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");

    $this->absencePeriods[] = AbsencePeriodFabricator::fabricate([
      'name' => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $this->absencePeriods[] = AbsencePeriodFabricator::fabricate([
      'name' => 'Period 2',
      'start_date' => CRM_Utils_Date::processDate('2017-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2017-12-31'),
    ]);

    $this->absencePeriods[] = AbsencePeriodFabricator::fabricate([
      'name' => 'Period 3',
      'start_date' => CRM_Utils_Date::processDate('2018-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2018-12-31'),
    ]);
  }

  public function testGetAbsencePeriodWithoutDatesReturnsAllTheAbsencePeriods() {
    $periods = AbsencePeriod::getAbsencePeriods();
    // Assert only that the number of items is the expected one,
    // We cannot assert anything else because getAbsencePeriod() only returns
    // dates and in a different format than the one used to create the object
    $this->assertCount(count($this->absencePeriods), $periods);
  }

  public function testGetAbsencePeriodWithOnlyTheStartDateReturnsPeriodsOverlappingTheGivenDate() {
    $periods = AbsencePeriod::getAbsencePeriods('2017-10-13');
    // This should return 2017 and 2018
    $this->assertCount(2, $periods);
  }

  public function testGetAbsencePeriodWithBothStartAndEndDatesReturnsPeriodsOverlappingTheGivenDates() {
    $periods = AbsencePeriod::getAbsencePeriods('2017-10-13', '2017-12-31');
    // This should return only 2017
    $this->assertCount(1, $periods);
  }
}
