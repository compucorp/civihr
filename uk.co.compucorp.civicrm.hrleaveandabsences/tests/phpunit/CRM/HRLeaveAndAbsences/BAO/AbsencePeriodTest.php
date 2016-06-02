<?php

use Civi\Test\HeadlessInterface;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_AbsencePeriodTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_AbsencePeriodTest extends CiviUnitTestCase implements HeadlessInterface {

  protected $_tablesToTruncate = [
    'civicrm_hrleaveandabsences_absence_period',
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
}
