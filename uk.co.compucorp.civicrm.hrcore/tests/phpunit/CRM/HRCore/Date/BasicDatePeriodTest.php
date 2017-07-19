<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRCore_Date_BasicDatePeriod as BasicDatePeriod;

/**
 * @group headless
 */
class CRM_HRCore_Date_BasicDatePeriodTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, HookInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testItIncludesTheEndDate() {
    $period = new BasicDatePeriod('2016-01-01', '2016-01-05');
    $dates = $this->getDatesArrayForPeriod($period);

    $this->assertCount(5, $dates);
    $this->assertEquals('2016-01-01', $dates[0]->format('Y-m-d'));
    $this->assertEquals('2016-01-02', $dates[1]->format('Y-m-d'));
    $this->assertEquals('2016-01-03', $dates[2]->format('Y-m-d'));
    $this->assertEquals('2016-01-04', $dates[3]->format('Y-m-d'));
    $this->assertEquals('2016-01-05', $dates[4]->format('Y-m-d'));
  }

  public function testItCanBeInstantiatedWithDateTimeObjects() {
    $period = new BasicDatePeriod(new DateTime('2016-01-01'), new DateTime('2016-01-05'));
    $dates = $this->getDatesArrayForPeriod($period);

    $this->assertCount(5, $dates);
    $this->assertEquals('2016-01-01', $dates[0]->format('Y-m-d'));
    $this->assertEquals('2016-01-02', $dates[1]->format('Y-m-d'));
    $this->assertEquals('2016-01-03', $dates[2]->format('Y-m-d'));
    $this->assertEquals('2016-01-04', $dates[3]->format('Y-m-d'));
    $this->assertEquals('2016-01-05', $dates[4]->format('Y-m-d'));
  }

  public function testItDoesNotModifyTheGivenEndDate() {
    $startDate = new DateTime('2016-10-10');
    $endDate = new DateTime('2016-10-11');

    $period = new BasicDatePeriod($startDate, $endDate);
    $dates = $this->getDatesArrayForPeriod($period);

    $this->assertCount(2, $dates);
    $this->assertEquals('2016-10-10', $dates[0]->format('Y-m-d'));
    $this->assertEquals('2016-10-11', $dates[1]->format('Y-m-d'));

    $this->assertEquals('2016-10-11', $endDate->format('Y-m-d'));
  }

  public function testGetStartDateReturnsCorrectly() {
    $startDate = new DateTime('2016-10-10');
    $endDate = new DateTime('2016-10-11');

    $period = new BasicDatePeriod($startDate, $endDate);
    $this->assertEquals($period->getStartDate(), $startDate);
  }

  public function testGetStartDateReturnsCorrectlyWhenTheConstructorAcceptsDateAsStrings() {
    $startDate = '2016-10-10';
    $endDate = '2016-10-11';

    $period = new BasicDatePeriod($startDate, $endDate);
    $this->assertEquals($period->getStartDate(), new DateTime($startDate));
  }

  public function testGetEndDateReturnsCorrectlyWhenTheConstructorAcceptsDateAsStrings() {
    $startDate = '2016-10-10';
    $endDate = '2016-10-11';

    $period = new BasicDatePeriod($startDate, $endDate);
    $this->assertEquals($period->getEndDate(), new DateTime($endDate));
  }

  public function testGetEndDateReturnsCorrectly() {
    $startDate = new DateTime('2016-10-10');
    $endDate = new DateTime('2016-10-11');

    $period = new BasicDatePeriod($startDate, $endDate);
    $this->assertEquals($period->getEndDate(), $endDate);
  }

  public function testAdjustDatesToMatchPeriodDatesShouldAdjustStartDateIfItsLessThanThePeriodStartDate() {
    $startDate = new DateTime('2016-10-10');
    $endDate = new DateTime('2016-10-11');
    $period = new BasicDatePeriod($startDate, $endDate);

    $fromDate = '2016-10-05';
    $toDate = '2016-10-11';

    $adjustedPeriod = $period->adjustDatesToMatchPeriodDates($fromDate, $toDate);
    //fromDate is less than the period start date therefore period start date will be returned
    $this->assertEquals($period->getStartDate(), $adjustedPeriod->getStartDate());
    $this->assertEquals($period->getEndDate(), $adjustedPeriod->getEndDate());

  }

  public function testAdjustDatesToMatchPeriodDatesShouldAdjustEndDateIfItsGreaterThanThePeriodEndDate() {
    $startDate = new DateTime('2016-10-10');
    $endDate = new DateTime('2016-10-11');
    $period = new BasicDatePeriod($startDate, $endDate);

    $fromDate = '2016-10-10';
    $toDate = '2016-10-12';

    $adjustedPeriod = $period->adjustDatesToMatchPeriodDates($fromDate, $toDate);
    //toDate is greater than the period end date therefore period end date will be returned
    $this->assertEquals($period->getStartDate(), $adjustedPeriod->getStartDate());
    $this->assertEquals($period->getEndDate(), $adjustedPeriod->getEndDate());
  }

  public function testAdjustDatesShouldReturnTheDatesUnModifiedIfStartDateIsGreaterThanPeriodStartDateAndEndDateLessThanPeriodEndDate() {
    $startDate = new DateTime('2016-10-10');
    $endDate = new DateTime('2016-10-14');
    $period = new BasicDatePeriod($startDate, $endDate);

    $fromDate = '2016-10-12';
    $toDate = '2016-10-13';

    $adjustedPeriod = $period->adjustDatesToMatchPeriodDates($fromDate, $toDate);
    //toDate is less than the period end date therefore toDate will be returned
    //fromDate is greater than period start date therefore fromDate will be returned
    $this->assertEquals($fromDate, $adjustedPeriod->getStartDate()->format('Y-m-d'));
    $this->assertEquals($toDate, $adjustedPeriod->getEndDate()->format('Y-m-d'));
  }

  private function getDatesArrayForPeriod(BasicDatePeriod $period) {
    $dates = [];
    foreach($period as $date) {
      $dates[] = $date;
    }

    return $dates;
  }
}
