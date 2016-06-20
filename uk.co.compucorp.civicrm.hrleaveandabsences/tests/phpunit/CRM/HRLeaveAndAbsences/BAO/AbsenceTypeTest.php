<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_AbsenceTypeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_AbsenceTypeTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface, TransactionalInterface {

  private $allColors = [
      '#5A6779', '#E5807F', '#ECA67F', '#8EC68A', '#C096AA', '#9579A8', '#42B0CB',
      '#3D4A5E', '#E56A6A', '#FA8F55', '#6DAD68', '#B37995', '#84619C', '#2997B3',
      '#263345', '#CC4A49', '#D97038', '#4F944A', '#995978', '#5F3D76', '#147E99',
      '#151D2C', '#B32E2E', '#BF561D', '#377A31', '#803D5E', '#47275C', '#056780'
  ];

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
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

  public function testShouldHaveAllTheColorsAvailableIfTheresNotTypeCreated() {
    $availableColors = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getAvailableColors();
    foreach($this->allColors as $color) {
      $this->assertContains($color, $availableColors);
    }
  }

  public function testShouldNotAllowColorToBeReusedUntilAllColorsHaveBeenUsed() {
    $usedColors = [];
    $numberOfColors = count($this->allColors);
    for($i = 0; $i < $numberOfColors; $i++) {
      $color = $this->allColors[$i];
      $this->createBasicType(['color' => $color]);
      $usedColors[] = $color;
      $availableColors = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getAvailableColors();

      $isLastColor = ($i == $numberOfColors - 1);
      foreach($usedColors as $usedColor) {
        if($isLastColor) {
          $this->assertContains($usedColor, $availableColors);
        } else {
          $this->assertNotContains($usedColor, $availableColors);
        }
      }
    }
  }

  public function testIsReservedCannotBeSetOnCreate() {
    $entity = $this->createBasicType(['is_reserved' => 1]);
    $this->assertEquals(0, $entity->is_reserved);
  }

  public function testIsReservedCannotBeSetOnUpdate() {
    $entity = $this->createBasicType();
    $this->assertEquals(0, $entity->is_reserved);
    $entity = $this->updateBasicType($entity->id, ['is_reserved' => 1]);
    $this->assertEquals(0, $entity->is_reserved);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_OperationNotAllowedException
   * @expectedExceptionMessage Reserved types cannot be deleted!
   */
  public function testShouldNotBeAllowedToDeleteReservedTypes()
  {
    $id = $this->createReservedType();
    $this->assertNotNull($id);
    CRM_HRLeaveAndAbsences_BAO_AbsenceType::del($id);
  }

  public function testShouldBeAllowedToDeleteReservedTypes()
  {
    $entity = $this->createBasicType();
    $this->assertNotNull($entity->id);
    CRM_HRLeaveAndAbsences_BAO_AbsenceType::del($entity->id);
    $entity = $this->findTypeByID($entity->id);
    $this->assertNull($entity);
  }

  public function testGetValuesArrayShouldReturnAbsenceTypeValues() {
    $params = [
      'title'                               => 'Title 1',
      'color'                               => '#000101',
      'default_entitlement'                 => 21,
      'allow_request_cancelation'           => 1,
      'is_active'                           => 1,
      'is_default'                          => 1,
      'allow_carry_forward'                 => 1,
      'max_number_of_days_to_carry_forward' => 10,
    ];
    $entity = $this->createBasicType($params);
    $values = CRM_HRLeaveAndAbsences_BAO_AbsenceType::getValuesArray($entity->id);
    foreach ($params as $field => $value) {
      $this->assertEquals($value, $values[$field]);
    }
  }

  public function testHasExpirationDuration()
  {
    $absenceType1 = $this->createBasicType([
      'allow_carry_forward' => true
    ]);

    $absenceType2 = $this->createBasicType([
      'allow_carry_forward' => true,
      'carry_forward_expiration_duration' => 3,
      'carry_forward_expiration_unit' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_DAYS,
    ]);

    $this->assertFalse($absenceType1->hasExpirationDuration());
    $this->assertTrue($absenceType2->hasExpirationDuration());
  }

  public function testCarryForwardNeverExpiresShouldReturnTrueIfTypeHasNoExpirationDuration()
  {
    $absenceType = $this->createBasicType(['allow_carry_forward' => true]);
    $this->assertTrue($absenceType->carryForwardNeverExpires());
  }

  public function testCarryForwardNeverExpiresShouldReturnFalseIfTypeHasExpirationDuration()
  {
    $absenceType = $this->createBasicType([
      'allow_carry_forward' => true,
      'carry_forward_expiration_duration' => 4,
      'carry_forward_expiration_unit' => CRM_HRLeaveAndAbsences_BAO_AbsenceType::EXPIRATION_UNIT_YEARS
    ]);
    $this->assertFalse($absenceType->carryForwardNeverExpires());
  }

  public function testCarryForwardNeverExpiresShouldBeNullIfTypeDoesAllowCarryForward()
  {
    $absenceType = $this->createBasicType(['allow_carry_forward' => false]);
    $this->assertNull($absenceType->carryForwardNeverExpires());
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

    if($entity->N == 0) {
      return null;
    }

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

  /**
   * Since we cannot create reserved types through the API,
   * we have this helper method to insert one directly in
   * the database
   */
  private function createReservedType()
  {
    $title = 'Title ' . microtime();
    $query = "
      INSERT INTO
        civicrm_hrleaveandabsences_absence_type(title, color, default_entitlement, allow_request_cancelation, is_reserved, weight)
        VALUES('{$title}', '#000000', 0, 1, 1, 1)
    ";
    CRM_Core_DAO::executeQuery($query);

    $query = "SELECT id FROM civicrm_hrleaveandabsences_absence_type WHERE title = '{$title}'";
    $dao = CRM_Core_DAO::executeQuery($query);
    if($dao->N == 1) {
      $dao->fetch();
      return $dao->id;
    }

    return null;
  }
}
