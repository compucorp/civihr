<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * Class api_v3_AbsenceTypeTest
 *
 * @group headless
 */
class api_v3_AbsenceTypeTest extends BaseHeadlessTest {

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: date
   */
  public function testOnCalculateToilExpiryDateDoesNotAcceptAbsenceTypeIdWithoutDate() {
    civicrm_api3('AbsenceType', 'calculatetoilexpirydate', ['absence_type_id' => 1]);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Mandatory key(s) missing from params array: absence_type_id
   */
  public function testOnCalculateToilExpiryDateDoesNotAcceptDateWithoutAbsenceTypeId() {
    civicrm_api3('AbsenceType', 'calculatetoilexpirydate', ['date' => '2016-10-05']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage date is not a valid date: 2016-30-05
   */
  public function testOnCalculateToilExpiryDateDoesNotAcceptInvalidDate() {
    civicrm_api3('AbsenceType', 'calculatetoilexpirydate', ['absence_type_id' => 1, 'date' => '2016-30-05']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Unable to find a CRM_HRLeaveAndAbsences_BAO_AbsenceType with id 20.
   */
  public function testOnCalculateToilExpiryDateWhenAbsenceTypeIsInvalid() {
    civicrm_api3('AbsenceType', 'calculatetoilexpirydate', ['absence_type_id' => 20, 'date' => '2016-10-05']);
  }

  public function testCalculateToilExpiryDateWhenAbsenceTypeAllowsAccrualsRequestAndNeverExpires() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'accrual_expiration_unit' => null,
      'accrual_expiration_duration' => null,
      'is_active' => 1,
    ]);
    //date to calculate TOIL expiry for
    $date = '2016-11-10';
    $result = civicrm_api3('AbsenceType', 'calculatetoilexpirydate', ['absence_type_id' => $absenceType->id, 'date' => $date]);
    $expected_result = ['expiry_date' => false];
    $this->assertEquals($expected_result, $result['values']);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage This Absence Type does not allow Accruals Request
   */
  public function testCalculateToilExpiryDateWhenAbsenceTypeDoesNotAllowAccrualsRequest() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => false,
      'is_active' => 1,
    ]);
    //date to calculate TOIL expiry for
    $date = '2016-11-10';
    civicrm_api3('AbsenceType', 'calculatetoilexpirydate', ['absence_type_id' => $absenceType->id, 'date' => $date]);
  }

  public function testCalculateToilExpiryDateWhenAbsenceTypeAllowsAccrualsRequestAndExpiryDurationSet() {
    //Duration set in days
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'accrual_expiration_duration' => 10,
      'accrual_expiration_unit' => AbsenceType::EXPIRATION_UNIT_DAYS,
      'is_active' => 1,
    ]);
    //date to calculate TOIL expiry for
    $date = '2016-11-10';
    $result = civicrm_api3('AbsenceType', 'calculatetoilexpirydate', ['absence_type_id' => $absenceType->id, 'date' => $date]);
    $expected_result = ['expiry_date' => '2016-11-20'];
    $this->assertEquals($expected_result, $result['values']);

    //Duration set in months
    $absenceType2 = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 2',
      'allow_accruals_request' => true,
      'accrual_expiration_duration' => 10,
      'accrual_expiration_unit' => AbsenceType::EXPIRATION_UNIT_MONTHS,
      'is_active' => 1,
    ]);
    //date to calculate TOIL expiry for
    $date = '2016-11-10';
    $result = civicrm_api3('AbsenceType', 'calculatetoilexpirydate', ['absence_type_id' => $absenceType2->id, 'date' => $date]);
    $expected_result = ['expiry_date' => '2017-09-10'];
    $this->assertEquals($expected_result, $result['values']);
  }
}
