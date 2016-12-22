<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;

/**
 * Class api_v3_TOILRequestTest
 *
 * @group headless
 */
class api_v3_TOILRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_TOILRequestHelpersTrait;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    $this->toilAmounts = $this->toilAmountOptions();
  }

  public function testTOILRequestIsValidShouldReturnErrorWhenToilAmountIsNotValid() {
    $result = civicrm_api3('TOILRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'toil_to_accrue' => 200,
      'duration' => 120
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'toil_to_accrue' => ['toil_request_toil_amount_is_invalid']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testTOILRequestIsValidShouldReturnErrorWhenToilAmountIsGreaterThanMaximumAllowed() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 1,
      'is_active' => 1,
    ]);

    $result = civicrm_api3('TOILRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'to_date' => '2016-11-18',
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'toil_to_accrue' => ['toil_request_toil_amount_more_than_maximum_for_absence_type']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testTOILRequestIsValidShouldReturnErrorWhenTOILRequestIsMadeWithPastDatesAndAbsenceTypeDoesNotAllowPastDates() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
      'allow_accrue_in_the_past' => false
    ]);

    $result = civicrm_api3('TOILRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'to_date' => '2016-11-18',
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'from_date' => ['toil_request_toil_cannot_be_requested_for_past_days']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testTOILRequestIsValidShouldNotReturnErrorWhenValidationsPass() {

    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
    ]);

    $result = civicrm_api3('TOILRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 0,
      'values' => []
    ];
    $this->assertArraySubset($expectedResult, $result);
  }
}
