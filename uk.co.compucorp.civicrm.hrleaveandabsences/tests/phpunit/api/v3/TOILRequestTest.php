<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;

/**
 * Class api_v3_TOILRequestTest
 *
 * @group headless
 */
class api_v3_TOILRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_TOILRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    $this->toilAmounts = $this->toilAmountOptions();
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
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

  public function testToilRequestGetShouldReturnAssociatedLeaveRequestData() {
    $fromDate1 = new DateTime("2016-11-14");
    $toDate1 = new DateTime("2016-11-17");

    $fromDate2 = new DateTime("2016-11-20");
    $toDate2 = new DateTime("2016-11-30");

    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
    ]);

    $toilRequest1 = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => $toType,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 60
    ], false);

    $toilRequest2 = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => $toType,
      'toil_to_accrue' => $this->toilAmounts['3 Days']['value'],
      'duration' => 120
    ], false);

    $expectedResult = [
      [
        'id' => $toilRequest1->id,
        'type_id' => $absenceType->id,
        'contact_id' => 1,
        'status_id' => 1,
        'from_date' => $fromDate1->format('Y-m-d'),
        'from_date_type' => $fromType,
        'to_date' => $toDate1->format('Y-m-d'),
        'to_date_type' => $toType,
        'leave_request_id' => $toilRequest1->leave_request_id,
        'duration' => 60,
      ],
      [
        'id' => $toilRequest2->id,
        'type_id' => $absenceType->id,
        'contact_id' => 1,
        'status_id' => 1,
        'from_date' => $fromDate2->format('Y-m-d'),
        'from_date_type' => $fromType,
        'to_date' => $toDate2->format('Y-m-d'),
        'to_date_type' => $toType,
        'leave_request_id' => $toilRequest2->leave_request_id,
        'duration' => 120,
      ]
    ];

    $result = civicrm_api3('TOILRequest', 'get', ['contact_id'=> 1, 'sequential' => 1]);
    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testTOILRequestIsValidShouldReturnErrorWhenDurationIsEmptyOrNotPresent() {
    $result = civicrm_api3('TOILRequest', 'isValid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'toil_to_accrue' => 200,
      'duration' => null
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'duration' => ['toil_request_duration_is_empty']
      ]
    ];

    $this->assertArraySubset($expectedResult, $result);

    $result = civicrm_api3('TOILRequest', 'isValid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'toil_to_accrue' => 200,
    ]);
    $this->assertArraySubset($expectedResult, $result);
  }

  public function testTOILRequestIsValidShouldReturnErrorWhenToilToAccrueIsEmptyOrNotPresent() {
    $result = civicrm_api3('TOILRequest', 'isValid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'toil_to_accrue' => '',
      'duration' => 10,
    ]);

    $expectedResult = [
      'is_error' => 0,
      'count' => 1,
      'values' => [
        'toil_to_accrue' => ['toil_request_toil_to_accrue_is_empty']
      ]
    ];
    $this->assertArraySubset($expectedResult, $result);

    $result = civicrm_api3('TOILRequest', 'isValid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'duration' => 10
    ]);
    $this->assertArraySubset($expectedResult, $result);
  }
}
