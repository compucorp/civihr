<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_HRLeaveAndAbsences_BAO_AbsenceTypeTest extends CiviUnitTestCase {

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
  public function testTypeTitlesShouldBeUnique() {
    $this->createBasicType(['title' => 'Type 1']);
    $this->createBasicType(['title' => 'Type 1']);
  }

  public function testThereShouldBeOnlyOneDefaultTypeOnCreate() {
    $basicEntity = $this->createBasicType(['is_default' => true]);
    $entity1 = $this->findTypeByID($basicEntity->id);
    $this->assertEquals(1, $entity1->is_default);

    $basicEntity = $this->createBasicType(['is_default' => true]);
    $entity2 = $this->findTypeByID($basicEntity->id);
    $entity1 = $this->findTypeByID($entity1->id);
    $this->assertEquals(0,  $entity1->is_default);
    $this->assertEquals(1, $entity2->is_default);
  }

  public function testThereShouldBeOnlyOneDefaultTypeOnUpdate() {
    $basicEntity1 = $this->createBasicType(['is_default' => false]);
    $basicEntity2 = $this->createBasicType(['is_default' => false]);
    $entity1 = $this->findTypeByID($basicEntity1->id);
    $entity2 = $this->findTypeByID($basicEntity2->id);
    $this->assertEquals(0,  $entity1->is_default);
    $this->assertEquals(0,  $entity2->is_default);

    $this->updateBasicType($basicEntity1->id, ['is_default' => true]);
    $entity1 = $this->findTypeByID($basicEntity1->id);
    $entity2 = $this->findTypeByID($basicEntity2->id);
    $this->assertEquals(1, $entity1->is_default);
    $this->assertEquals(0,  $entity2->is_default);

    $this->updateBasicType($basicEntity2->id, ['is_default' => true]);
    $entity1 = $this->findTypeByID($basicEntity1->id);
    $entity2 = $this->findTypeByID($basicEntity2->id);
    $this->assertEquals(0,  $entity1->is_default);
    $this->assertEquals(1, $entity2->is_default);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedException There is already one Absence Type where "Must staff take public holiday as leave" is selected
   */
  public function testThereShouldBeOnlyOneTypeWithAddPublicHolidayToEntitlementOnCreate() {
    $basicEntity = $this->createBasicType(['add_public_holiday_to_entitlement' => true]);
    $entity1 = $this->findTypeByID($basicEntity->id);
    $this->assertEquals(1, $entity1->add_public_holiday_to_entitlement);

    $this->createBasicType(['add_public_holiday_to_entitlement' => true]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedException There is already one Absence Type where "Must staff take public holiday as leave" is selected
   */
  public function testThereShouldBeOnlyOneTypeWithAddPublicHolidayToEntitlementOnUpdate() {
    $basicEntity1 = $this->createBasicType(['add_public_holiday_to_entitlement' => true]);
    $basicEntity2 = $this->createBasicType();
    $entity1 = $this->findTypeByID($basicEntity1->id);
    $entity2 = $this->findTypeByID($basicEntity2->id);
    $this->assertEquals(1, $entity1->add_public_holiday_to_entitlement);
    $this->assertEquals(0, $entity2->add_public_holiday_to_entitlement);

    $this->updateBasicType($basicEntity2->id, ['add_public_holiday_to_entitlement' => true]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To set maximum amount of leave that can be accrued you must allow staff to accrue additional days
   */
  public function testAllowAccrualsRequestShouldBeTrueIfMaxLeaveAccrualIsNotEmpty() {
    $this->createBasicType([
        'allow_accruals_request' => false,
        'max_leave_accrual' => 1
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To allow accrue in the past you must allow staff to accrue additional days
   */
  public function testAllowAccrualsRequestShouldBeTrueIfAllowAccrueInThePast() {
    $this->createBasicType([
        'allow_accruals_request'   => false,
        'allow_accrue_in_the_past' => 1
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To set the accrual expiry duration you must allow staff to accrue additional days
   */
  public function testAllowAccrualsRequestShouldBeTrueIfAllowAccrualDurationAndUnitAreNotEmpty() {
    $this->createBasicType([
        'allow_accruals_request' => false,
        'accrual_expiration_duration' => 1,
        'accrual_expiration_unit' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_DAYS
    ]);
  }

  /**
   * @dataProvider expirationUnitDataProvider
   */
  public function testShouldNotAllowInvalidAccrualExpirationUnit($expirationUnit, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
          CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException::class,
          'Invalid Accrual Expiration Unit'
      );
    }

    $this->createBasicType([
        'allow_accruals_request' => true,
        'accrual_expiration_duration' => 1,
        'accrual_expiration_unit' => $expirationUnit
    ]);
  }

  /**
   * @dataProvider accrualExpirationUnitAndDurationDataProvider
   */
  public function testShouldNotAllowAccrualExpirationUnitWithoutDurationAndViceVersa($unit, $duration, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
          CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException::class,
          'Invalid Accrual Expiration. It should have both Unit and Duration'
      );
    }

    $this->createBasicType([
        'allow_accruals_request' => true,
        'accrual_expiration_unit' => $unit,
        'accrual_expiration_duration' => $duration,
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To set the Max Number of Days to Carry Forward you must allow Carry Forward
   */
  public function testAllowCarryForwardShouldBeTrueIfMaxNumberOfDaysToCarryForwardIsNotEmpty() {
    $this->createBasicType([
        'allow_carry_forward'   => false,
        'max_number_of_days_to_carry_forward' => 1
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To set the Carry Forward Expiration Date you must allow Carry Forward
   */
  public function testAllowCarryForwardShouldBeTrueIfCarryForwardExpirationDayAndMonthAreNotEmpty() {
    $this->createBasicType([
        'allow_carry_forward'   => false,
        'carry_forward_expiration_day' => 10,
        'carry_forward_expiration_month' => 4,
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage To set the carry forward expiry duration you must allow Carry Forward
   */
  public function testAllowCarryForwardShouldBeTrueIfCarryForwardExpirationDurationAndUnitAreNotEmpty() {
    $this->createBasicType([
        'allow_carry_forward' => false,
        'carry_forward_expiration_duration' => 1,
        'carry_forward_expiration_unit' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_DAYS
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException
   * @expectedExceptionMessage You can't set both the Carry Forward Expiration Date and Period
   */
  public function testCarryForwardExpirationDateAndPeriodCannotBothBeNotEmpty() {
    $this->createBasicType([
      'allow_carry_forward' => true,
      'carry_forward_expiration_duration' => 1,
      'carry_forward_expiration_unit' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_YEARS,
      'carry_forward_expiration_day' => 15,
      'carry_forward_expiration_month' => 4,
    ]);
  }

  /**
   * @dataProvider accrualExpirationUnitAndDurationDataProvider
   */
  public function testShouldNotAllowCarryForwardExpirationUnitWithoutDurationAndViceVersa($unit, $duration, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
          CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException::class,
          'Invalid Carry Forward Expiration. It should have both Unit and Duration'
      );
    }

    $this->createBasicType([
        'allow_carry_forward' => true,
        'carry_forward_expiration_unit' => $unit,
        'carry_forward_expiration_duration' => $duration,
    ]);
  }

  /**
   * @dataProvider carryForwardExpirationDateDataProvider
   */
  public function testCarryForwardExpirationDateIsValid($day, $month, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
          CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException::class,
          'Invalid Carry Forward Expiration Date'
      );
    }

    $this->createBasicType([
        'allow_carry_forward' => true,
        'carry_forward_expiration_day' => $day,
        'carry_forward_expiration_month' => $month
    ]);
  }

  /**
   * @dataProvider expirationUnitDataProvider
   */
  public function testShouldNotAllowInvalidCarryForwardExpirationUnit($expirationUnit, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
          CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException::class,
          'Invalid Carry Forward Expiration Unit'
      );
    }

    $this->createBasicType([
        'allow_carry_forward' => true,
        'carry_forward_expiration_duration' => 1,
        'carry_forward_expiration_unit' => $expirationUnit
    ]);
  }

  /**
   * @dataProvider allowRequestCancelationDataProvider
   */
  public function testShouldNotAllowInvalidRequestCancelationOptions($requestCancelationOption, $throwsException) {
    if($throwsException) {
      $this->setExpectedException(
          CRM_HRLeaveAndAbsences_Exception_InvalidAbsenceTypeException::class,
          'Invalid Request Cancelation Option'
      );
    }

    $this->createBasicType(['allow_request_cancelation' => $requestCancelationOption]);
  }

  public function testWeightShouldAlwaysBeMaxWeightPlus1OnCreate()
  {
    $firstEntity = $this->createBasicType();
    $this->assertNotEmpty($firstEntity->weight);

    $secondEntity = $this->createBasicType();
    $this->assertNotEmpty($secondEntity->weight);
    $this->assertEquals($firstEntity->weight + 1, $secondEntity->weight);
  }
  
  private function createBasicType($params = array()) {
    $basicRequiredFields = [
        'title' => 'Type ' . microtime(),
        'color' => '#000000',
        'default_entitlement' => 20,
        'allow_request_cancelation' => 1,
    ];

    $params = array_merge($basicRequiredFields, $params);
    return CRM_HRLeaveAndAbsences_BAO_AbsenceType::create($params);
  }

  private function updateBasicType($id, $params) {
    $params['id'] = $id;
    return $this->createBasicType($params);
  }

  private function findTypeByID($id) {
    $entity = new CRM_HRLeaveAndAbsences_BAO_AbsenceType();
    $entity->id = $id;
    $entity->find(true);

    return $entity;
  }

  public function expirationUnitDataProvider() {
    $data = [
        [rand(3, PHP_INT_MAX), true],
        [rand(3, PHP_INT_MAX), true],
    ];
    $validOptions = array_keys(CRM_HRLeaveAndAbsences_BAO_AbsenceType::getExpirationUnitOptions());
    foreach($validOptions as $option) {
      $data[] = [$option, false];
    }
    return $data;
  }

  public function accrualExpirationUnitAndDurationDataProvider() {
    return [
      [CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_DAYS, null, true],
      [null, 10, true],
      [CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_MONTHS, 5, false],
    ];
  }

  public function allowRequestCancelationDataProvider() {
    $data = [
        [rand(3, PHP_INT_MAX), true],
        [rand(3, PHP_INT_MAX), true],
    ];
    $validOptions = array_keys(CRM_HRLeaveAndAbsences_BAO_AbsenceType::getRequestCancelationOptions());
    foreach($validOptions as $option) {
      $data[] = [$option, false];
    }
    return $data;
  }

  public function carryForwardExpirationDateDataProvider() {
    return [
      [12, 12, false],
      [1, 2, false],
      [31, 1, false],
      [30, 2, true],
      [31, 4, true],
      [77, 9, true],
      [12, 31, true],
    ];
  }
}
