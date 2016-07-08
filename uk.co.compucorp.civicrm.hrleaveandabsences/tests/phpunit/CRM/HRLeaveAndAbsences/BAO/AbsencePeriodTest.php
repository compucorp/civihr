<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_AbsencePeriodTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_AbsencePeriodTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException
   * @expectedExceptionMessage Both the start and end dates are required
   *
   * @dataProvider startAndEndDatesDataProvider
   */
  public function testStartAndEndDateAreRequired($start_date, $end_date)
  {
    $this->createBasicPeriod([
      'title' => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate($start_date),
      'end_date' => CRM_Utils_Date::processDate($end_date)
    ]);
  }

  public function testWhenSavingAPeriodWithExistingWeightAllWeightsEqualOrGreaterShouldBeIncreased()
  {
    $period1 = $this->createBasicPeriod([
      'title'      => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-12-31'),
      'weight'    => 1
    ]);
    $period2 = $this->createBasicPeriod([
      'title'      => 'Period 2',
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
      'weight'    => 2
    ]);
    $period3 = $this->createBasicPeriod([
      'title'      => 'Period 3',
      'start_date' => CRM_Utils_Date::processDate('2017-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2017-12-31'),
      'weight'    => 2
    ]);

    $period1 = $this->findPeriodByID($period1->id);
    $period2 = $this->findPeriodByID($period2->id);
    $period3 = $this->findPeriodByID($period3->id);

    $this->assertEquals(1, $period1->weight);
    $this->assertEquals(3, $period2->weight);
    $this->assertEquals(2, $period3->weight);
  }

  public function testIfWeightIsEmptyItWillBeMaxWeightPlusOne()
  {
    $period1 = $this->createBasicPeriod([
      'title'      => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-12-31'),
      'weight'    => 1
    ]);
    $period2 = $this->createBasicPeriod([
      'title'      => 'Period 2',
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $period1 = $this->findPeriodByID($period1->id);
    $period2 = $this->findPeriodByID($period2->id);

    $this->assertEquals(1, $period1->weight);
    $this->assertEquals(2, $period2->weight);
  }

  /**
   * @expectedException PEAR_Exception
   * @expectedExceptionMessage DB Error: already exists
   */
  public function testPeriodsTitlesShouldBeUnique() {
    $this->createBasicPeriod([
      'title'      => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-12-31'),
    ]);
    $this->createBasicPeriod([
      'title'      => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);
  }

  /**
   * @dataProvider overlapingDatesDataProvider
   */
  public function testPeriodDatesCannotOverlapExistingPeriods($period1, $period2, $overlaps)
  {
    if($overlaps) {
      $this->setExpectedException(
        CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException::class,
        'This Absence Period overlaps with another existing Period'
      );
    }
    $this->createBasicPeriod([
      'title'      => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate($period1['start_date']),
      'end_date'   => CRM_Utils_Date::processDate($period1['end_date']),
    ]);
    $this->createBasicPeriod([
      'title'      => 'Period 2',
      'start_date' => CRM_Utils_Date::processDate($period2['start_date']),
      'end_date'   => CRM_Utils_Date::processDate($period2['end_date']),
    ]);
  }

  public function testPeriodCannotOverlapWithItself()
  {
    $startDateUnformatted = date('Y-m-d');
    $endDateUnformatted = date('Y-m-d', strtotime('+1 day'));
    $params = [
      'title' => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate($startDateUnformatted),
      'end_date' => CRM_Utils_Date::processDate($endDateUnformatted),
      'weight' => 1
    ];
    $period = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::create($params);

    $period = $this->findPeriodByID($period->id);
    $this->assertEquals($params['title'], $period->title);
    $this->assertEquals($startDateUnformatted, $period->start_date);
    $this->assertEquals($endDateUnformatted, $period->end_date);
    $this->assertEquals($params['weight'], $period->weight);

    // Saving the period keeping its start and end dates should not
    // throw an InvalidAbsencePeriod exception saying it overlaps
    // with another period (itself)
    $params['title'] = 'Period 1 Updated';
    $params['id'] = $period->id;
    CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::create($params);

    $period = $this->findPeriodByID($period->id);
    $this->assertEquals($params['title'], $period->title);
    $this->assertEquals($startDateUnformatted, $period->start_date);
    $this->assertEquals($endDateUnformatted, $period->end_date);
    $this->assertEquals($params['weight'], $period->weight);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException
   * @expectedExceptionMessage Both the start and end dates should be valid
   *
   * @dataProvider startAndEndInvalidDatesDataProvider
   */
  public function testStartAndEndDatesShouldBeValidDates($start_date, $end_date)
  {
    $this->createBasicPeriod([
      'title' => 'Period 1',
      'start_date' => $start_date,
      'end_date' => $end_date,
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsencePeriodException
   * @expectedExceptionMessage Start Date should be less than End Date
   *
   * @dataProvider startDateGreaterEndDateDataProvider
   */
  public function testStartDateShouldNotBeGreaterOrEqualThanEndDate($start_date, $end_date)
  {
    $this->createBasicPeriod([
      'title' => 'Period 1',
      'start_date' => CRM_Utils_Date::processDate($start_date),
      'end_date'   => CRM_Utils_Date::processDate($end_date)
    ]);
  }

  public function testGetValuesArrayShouldReturnAbsencePeriodValues()
  {
    $startDateUnformatted = date('Y-m-d');
    $endDateUnformatted = date('Y-m-d', strtotime('+6 months'));
    $params = [
      'title' => 'Period Title',
      'start_date' => CRM_Utils_Date::processDate($startDateUnformatted),
      'end_date' => CRM_Utils_Date::processDate($endDateUnformatted)
    ];
    $entity = $this->createBasicPeriod($params);
    $values = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getValuesArray($entity->id);
    $this->assertEquals($params['title'], $values['title']);
    $this->assertEquals($startDateUnformatted, $values['start_date']);
    $this->assertEquals($endDateUnformatted, $values['end_date']);
  }

  public function testItCanReturnTheMostRecentStartDateAvailable()
  {
    $date = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getMostRecentStartDateAvailable();
    $this->assertEquals(date('Y-m-d'), $date);

    $this->createBasicPeriod([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2015-12-31'),
    ]);
    $date = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getMostRecentStartDateAvailable();
    $this->assertEquals('2016-01-01', $date);

    $this->createBasicPeriod([
      'start_date' => CRM_Utils_Date::processDate('2014-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2014-01-31'),
    ]);
    $date = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getMostRecentStartDateAvailable();
    $this->assertEquals('2016-01-01', $date);

    $this->createBasicPeriod([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date' => CRM_Utils_Date::processDate('2016-12-31'),
    ]);
    $date = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getMostRecentStartDateAvailable();
    $this->assertEquals('2017-01-01', $date);
  }

  /**
   * @dataProvider periodsToCalculateNumberOfWorkingDays
   */
  public function testCanCalculateTheNumberOfWorkingDays($startDate, $endDate, $expectedNumberOfWorkingDays)
  {
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = $startDate;
    $period->end_date = $endDate;
    $this->assertEquals($expectedNumberOfWorkingDays, $period->getNumberOfWorkingDays());
  }

  /**
   * @expectedException UnexpectedValueException
   * @expectedExceptionMessage You can only get the number of working days for an AbsencePeriod with a valid start date
   */
  public function testCannotCalculateTheNumberOfWorkingDaysForAPeriodWithAnEmptyStartDate()
  {
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->end_date = date('Y-m-d');
    $period->getNumberOfWorkingDays();
  }

  /**
   * @expectedException UnexpectedValueException
   * @expectedExceptionMessage You can only get the number of working days for an AbsencePeriod with a valid end date
   */
  public function testCannotCalculateTheNumberOfWorkingDaysForAPeriodWithAnEmptyEndDate()
  {
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = date('Y-m-d');
    $period->getNumberOfWorkingDays();
  }

  public function testCanCalculateTheNumberOfWorkingDaysWithPublicHolidays()
  {
    $start_date = '2016-01-01';
    $end_date = '2016-12-31';

    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = $start_date;
    $period->end_date = $end_date;
    $this->assertEquals(261, $period->getNumberOfWorkingDays());

    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::clearNumberOfPublicHolidaysForPeriodCache();
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public Holiday 1',
      'date' => CRM_Utils_Date::processDate('2016-01-01')
    ]);
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = $start_date;
    $period->end_date = $end_date;
    $this->assertEquals(260, $period->getNumberOfWorkingDays());

    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::clearNumberOfPublicHolidaysForPeriodCache();
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public Holiday 2',
      'date' => CRM_Utils_Date::processDate('2016-05-17')
    ]);
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = $start_date;
    $period->end_date = $end_date;
    $this->assertEquals(259, $period->getNumberOfWorkingDays());

    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::clearNumberOfPublicHolidaysForPeriodCache();
    // A public holiday on a weekend should not be counted
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public Holiday On Weekend',
      'date' => CRM_Utils_Date::processDate('2016-12-25')
    ]);
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = $start_date;
    $period->end_date = $end_date;
    $this->assertEquals(259, $period->getNumberOfWorkingDays());
  }

  public function testCanCalculateTheNumberOfWorkingDaysToWork()
  {
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = '2016-01-01';
    $period->end_date = '2016-12-31';

    $this->assertEquals(22, $period->getNumberOfWorkingDaysToWork('2016-05-01', '2016-05-31'));
    $this->assertEquals(1, $period->getNumberOfWorkingDaysToWork('2015-10-01', '2016-01-02'));
    $this->assertEquals(5, $period->getNumberOfWorkingDaysToWork('2016-12-25', '2017-12-25'));
  }

  public function testCanCalculateTheNumberOfWorkingDaysToWorkWithPublicHolidays()
  {
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = '2016-01-01';
    $period->end_date = '2016-12-31';

    $this->assertEquals(22, $period->getNumberOfWorkingDaysToWork('2016-05-01', '2016-05-31'));

    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public Holiday 1',
      'date' => CRM_Utils_Date::processDate('2016-05-02')
    ]);
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public Holiday 2',
      'date' => CRM_Utils_Date::processDate('2016-05-30')
    ]);
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::clearNumberOfPublicHolidaysForPeriodCache();

    $this->assertEquals(20, $period->getNumberOfWorkingDaysToWork('2016-05-01', '2016-05-31'));
    $this->assertEquals(1, $period->getNumberOfWorkingDaysToWork('2015-10-01', '2016-01-02'));

    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public Holiday 3',
      'date' => CRM_Utils_Date::processDate('2015-10-01')
    ]);
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::clearNumberOfPublicHolidaysForPeriodCache();

    $this->assertEquals(1, $period->getNumberOfWorkingDaysToWork('2015-10-01', '2016-01-02'));

    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::create([
      'title' => 'Public Holiday 4',
      'date' => CRM_Utils_Date::processDate('2016-01-01')
    ]);
    CRM_HRLeaveAndAbsences_BAO_PublicHoliday::clearNumberOfPublicHolidaysForPeriodCache();

    $this->assertEquals(0, $period->getNumberOfWorkingDaysToWork('2015-10-01', '2016-01-02'));
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage getNumberOfWorkingDaysToWork expects a valid startDate in Y-m-d format
   *
   * @dataProvider invalidDatesDataProvider
   */
  public function testCannotCalculateTheNumberOfWorkingDaysToWorkForAnInvalidStartDate($startDate)
  {
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = '2016-01-01';
    $period->end_date = '2016-12-31';

    $period->getNumberOfWorkingDaysToWork($startDate, '2016-05-31');
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage getNumberOfWorkingDaysToWork expects a valid endDate in Y-m-d format
   *
   * @dataProvider invalidDatesDataProvider
   */
  public function testCannotCalculateTheNumberOfWorkingDaysToWorkForAnInvalidEndDate($endDate)
  {
    $period             = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = '2016-01-01';
    $period->end_date   = '2016-12-31';

    $period->getNumberOfWorkingDaysToWork('2016-01-01', $endDate);
  }

  public function testPreviousPeriodShouldReturnNullIfTheresNoPreviousPeriod()
  {
    $period = $this->createBasicPeriod();
    $this->assertNull($period->getPreviousPeriod());
  }

  public function testPreviousPeriodShouldReturnAnAbsencePeriodInstanceWhenThereIsAPreviousPeriod()
  {
    $period1 = $this->createBasicPeriod([
      'start_date' => CRM_Utils_Date::processDate('2015-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-02')
    ]);

    $period2 = $this->createBasicPeriod([
      'start_date' => CRM_Utils_Date::processDate('2015-01-03'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-04')
    ]);

    $period3 = $this->createBasicPeriod([
      'start_date' => CRM_Utils_Date::processDate('2015-01-05'),
      'end_date'   => CRM_Utils_Date::processDate('2015-01-06')
    ]);

    $period2PreviousPeriod = $period2->getPreviousPeriod();
    $period3PreviousPeriod = $period3->getPreviousPeriod();

    $period1 = $this->findPeriodByID($period1->id);
    $this->assertInstanceOf('CRM_HRLeaveAndAbsences_BAO_AbsencePeriod', $period2PreviousPeriod);
    $this->assertEquals($period1->id, $period2PreviousPeriod->id);
    $this->assertEquals($period1->title, $period2PreviousPeriod->title);
    $this->assertEquals($period1->start_date, $period2PreviousPeriod->start_date);
    $this->assertEquals($period1->end_date, $period2PreviousPeriod->end_date);
    $this->assertEquals($period1->weight, $period2PreviousPeriod->weight);

    $period2 = $this->findPeriodByID($period2->id);
    $this->assertInstanceOf('CRM_HRLeaveAndAbsences_BAO_AbsencePeriod', $period3PreviousPeriod);
    $this->assertEquals($period2->id, $period3PreviousPeriod->id);
    $this->assertEquals($period2->title, $period3PreviousPeriod->title);
    $this->assertEquals($period2->start_date, $period3PreviousPeriod->start_date);
    $this->assertEquals($period2->end_date, $period3PreviousPeriod->end_date);
    $this->assertEquals($period2->weight, $period3PreviousPeriod->weight);
  }

  public function testExpirationDateForAbsenceTypeWithoutCarryForwardShouldBeNull()
  {
    $type = new CRM_HRLeaveAndAbsences_BAO_AbsenceType();
    $type->allow_carry_forward = false;
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = '2016-01-01';
    $period->end_date = '2016-02-01';
    $this->assertNull($period->getExpirationDateForAbsenceType($type));
  }

  public function testExpirationDateForAbsenceTypeWithoutCarryForwardThatNeverExpiresShouldBeNull()
  {
    $type = new CRM_HRLeaveAndAbsences_BAO_AbsenceType();
    $type->allow_carry_forward = true;
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = '2016-01-01';
    $period->end_date = '2016-02-01';
    $this->assertNull($period->getExpirationDateForAbsenceType($type));
  }

  public function testCanCalculateExpirationDateForAbsenceTypeWithCarryForwardExpirationDuration()
  {
    $period = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $period->start_date = '2016-01-01';
    $period->end_date = '2016-02-01';

    $startDate = new DateTimeImmutable($period->start_date);

    $absenceType = new CRM_HRLeaveAndAbsences_BAO_AbsenceType();
    $absenceType->allow_carry_forward = true;

    //1 day duration
    $absenceType->carry_forward_expiration_unit = CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_DAYS;
    $absenceType->carry_forward_expiration_duration = 1;
    $expirationDate = $period->getExpirationDateForAbsenceType($absenceType);
    $expectedDate = $startDate->add(new DateInterval('P1D'));
    $this->assertEquals($expectedDate->format('Y-m-d'), $expirationDate);

    //5 days duration
    $absenceType->carry_forward_expiration_duration = 5;
    $expirationDate = $period->getExpirationDateForAbsenceType($absenceType);
    $expectedDate = $startDate->add(new DateInterval('P5D'));
    $this->assertEquals($expectedDate->format('Y-m-d'), $expirationDate);

    //5 months duration
    $absenceType->carry_forward_expiration_unit = CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_MONTHS;
    $expirationDate = $period->getExpirationDateForAbsenceType($absenceType);
    $expectedDate = $startDate->add(new DateInterval('P5M'));
    $this->assertEquals($expectedDate->format('Y-m-d'), $expirationDate);

    //10 months duration
    $absenceType->carry_forward_expiration_duration = 10;
    $expirationDate = $period->getExpirationDateForAbsenceType($absenceType);
    $expectedDate = $startDate->add(new DateInterval('P10M'));
    $this->assertEquals($expectedDate->format('Y-m-d'), $expirationDate);

    //10 years duration
    $absenceType->carry_forward_expiration_unit = CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_YEARS;
    $expirationDate = $period->getExpirationDateForAbsenceType($absenceType);
    $expectedDate = $startDate->add(new DateInterval('P10Y'));
    $this->assertEquals($expectedDate->format('Y-m-d'), $expirationDate);//10 years

    //1 year duration
    $absenceType->carry_forward_expiration_duration = 1;
    $expirationDate = $period->getExpirationDateForAbsenceType($absenceType);
    $expectedDate = $startDate->add(new DateInterval('P1Y'));
    $this->assertEquals($expectedDate->format('Y-m-d'), $expirationDate);
  }

  public function testGetCurrentPeriodShouldReturnTheCurrentPeriod()
  {
    $currentPeriod = $this->createBasicPeriod([
      'start_date' => date('YmdHis', strtotime('-2 days')),
      'end_date' => date('YmdHis', strtotime('+2 days')),
    ]);
    // Load the period from the database to make sure it was stored and
    // to get the dates in the Y-m-d format
    $currentPeriod = $this->findPeriodByID($currentPeriod->id);

    $this->createBasicPeriod([
      'start_date' => date('YmdHis', strtotime('-5 days')),
      'end_date' => date('YmdHis', strtotime('-3 days')),
    ]);

    $this->createBasicPeriod([
      'start_date' => date('YmdHis', strtotime('+3 days')),
      'end_date' => date('YmdHis', strtotime('+5 days')),
    ]);

    $returnedPeriod = CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getCurrentPeriod();
    $this->assertEquals($currentPeriod->id, $returnedPeriod->id);
    $this->assertEquals($currentPeriod->title, $returnedPeriod->title);
    $this->assertEquals($currentPeriod->start_date, $returnedPeriod->start_date);
    $this->assertEquals($currentPeriod->end_date, $returnedPeriod->end_date);
  }

  public function testGetCurrentPeriodShouldReturnNullIfThereIsNoPeriod()
  {
    $this->createBasicPeriod([
      'start_date' => date('YmdHis', strtotime('-5 days')),
      'end_date' => date('YmdHis', strtotime('-3 days')),
    ]);

    $this->createBasicPeriod([
      'start_date' => date('YmdHis', strtotime('+3 days')),
      'end_date' => date('YmdHis', strtotime('+5 days')),
    ]);
    $this->assertNull(CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getCurrentPeriod());
  }

  public function testGetCurrentPeriodShouldReturnNullIfThereIsNoCurrentPeriod()
  {
    $this->assertNull(CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::getCurrentPeriod());
  }

  private function createBasicPeriod($params = array()) {
    $basicRequiredFields = [
        'title' => 'Type ' . microtime(),
        'start_date' => CRM_Utils_Date::processDate(date('Y-m-d', strtotime('first day of this year'))),
        'end_date' => CRM_Utils_Date::processDate(date('Y-m-d', strtotime('last day of this year'))),
    ];

    $params = array_merge($basicRequiredFields, $params);
    return CRM_HRLeaveAndAbsences_BAO_AbsencePeriod::create($params);
  }

  private function findPeriodByID($id) {
    $entity = new CRM_HRLeaveAndAbsences_BAO_AbsencePeriod();
    $entity->id = $id;
    $entity->find(true);

    if($entity->N == 0) {
      return null;
    }

    return $entity;
  }

  public function overlapingDatesDataProvider()
  {
    return [
      [
        ['start_date' => '2015-01-01', 'end_date' => '2015-12-31'],
        ['start_date' => '2015-12-31', 'end_date' => '2016-02-01'],
        true
      ],
      [
        ['start_date' => '2015-01-01', 'end_date' => '2015-03-31'],
        ['start_date' => '2014-01-01', 'end_date' => '2015-01-02'],
        true
      ],
      [
        ['start_date' => '2015-03-01', 'end_date' => '2015-05-10'],
        ['start_date' => '2015-01-01', 'end_date' => '2015-12-31'],
        true
      ],
      [
        ['start_date' => '2015-01-01', 'end_date' => '2015-03-31'],
        ['start_date' => '2016-01-01', 'end_date' => '2016-02-01'],
        false
      ],
    ];
  }

  public function startAndEndDatesDataProvider()
  {
    return [
      [null, '2015-12-31'],
      ['2013-12-31', null],
      [null, null]
    ];
  }

  public function startAndEndInvalidDatesDataProvider()
  {
    return [
      ['2010-01-01', '2015-10-10'],
      ['2015-01-01', 'fdafdasfdsafdsafdsa'],
      ['232131232111', '2015-01-01'],
      ['2015-01-01', 12321321321],
      ['2015-02-31', '2014-01-01'],
      ['2015-01-01', '2015-13-01'],
      ['2015-02-31', 'dafsfdasfdasfdsafsd'],
      ['31/02/2015', 'dafsfdasfdasfdsafsd'],
      ['10/03/2014', '11/03/2015'],
      ['10/03/2014', '2015-01-01'],
      ['03/2017', '2020-10-11'],
      ['2020-10-11', '2016'],
      ['2020-10-11', '03/2017'],
    ];
  }

  public function startDateGreaterEndDateDataProvider()
  {
    return [
      ['2016-01-01', '2015-01-01'],
      ['2016-01-01', '2016-01-01'],
      ['2016-01-02', '2016-01-01'],
    ];
  }

  public function periodsToCalculateNumberOfWorkingDays()
  {
    return [
      ['2016-05-14', '2016-05-20', 5],
      ['2015-01-01', '2015-01-31', 22],
      ['2016-07-15', '2016-07-23', 6],
      ['2016-01-01', '2016-12-31', 261],
      ['2011-01-01', '2011-12-31', 260],
      ['2016-07-02', '2016-07-03', 0],
    ];
  }

  public function invalidDatesDataProvider()
  {
    return[
      ['dsaddfadlsfjdsal;kfjdsafdsa'],
      ['2016-02-30'],
      ['2016-30-10'],
      ['2016-03-45'],
      ['2016-02'],
      ['2016'],
      [1233321],
      [2016],
      [null]
    ];
  }
}
