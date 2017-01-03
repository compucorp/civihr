<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest as TOILRequestFabricator;

/**
 * Class api_v3_TOILRequestTest
 *
 * @group headless
 */
class api_v3_TOILRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_TOILRequestHelpersTrait;

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

  public function testCreateResponseAlsoIncludeTheLeaveRequestFields() {
    $contact = ContactFabricator::fabricate();

    $startDate = new DateTime();
    $endDate = new DateTime('+10 days');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => $startDate->format('Y-m-d')]
    );

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => $startDate->format('YmdHis'),
      'end_date' => $endDate->format('YmdHis')
    ]);

    $type = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 10
    ]);

    $leavePeriodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $period->id,
      'type_id' => $type->id,
    ]);

    $this->createLeaveBalanceChange($leavePeriodEntitlement->id, 10);

    $result = civicrm_api3('TOILRequest', 'create', [
      'contact_id' => $contact['id'],
      'type_id' => $type->id,
      'status_id' => $this->getLeaveRequestStatuses()['Approved']['value'],
      'from_date' => $startDate->format('Y-m-d'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'toil_to_accrue' => 3,
      'duration' => 60,
      'sequential' => 1
    ]);

    $expectedValues = [
      'contact_id' => $contact['id'],
      'type_id' => $type->id,
      'status_id' => $this->getLeaveRequestStatuses()['Approved']['value'],
      'from_date' => $startDate->format('Y-m-d'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => '',
      'to_date_type' => '',
      'duration' => 60
    ];

    $this->assertArraySubset($expectedValues, $result['values'][0]);
    $this->assertNotEmpty($result['values'][0]['id']);
    $this->assertNotEmpty($result['values'][0]['leave_request_id']);
  }

  public function testGetWithoutTheExpiredParamReturnsAllTOILRequests() {
    $contact = ContactFabricator::fabricate();

    $type = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 10
    ]);

    // This request has expired, but will be included on
    // the response since the "expired" flag is not set
    $toilRequest1 = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => $type->id,
      'from_date' => '20160101',
      'duration' => 10,
      'expiry_date' => '20160110'
    ]);

    $toilRequestBalanceChange = $this->findToilRequestBalanceChange($toilRequest1->id);
    LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $toilRequestBalanceChange->source_id,
      'source_type' => $toilRequestBalanceChange->source_type,
      'amount' => $toilRequestBalanceChange->amount * -1,
      'expiry_date' => CRM_Utils_Date::processDate($toilRequestBalanceChange->expiry_date),
      'expired_balance_change_id' => $toilRequestBalanceChange->id,
      'type_id' => $this->getBalanceChangeTypeValue('Debit')
    ]);

    $nextMonday = new DateTime('next monday');

    // This is not expired yet and it will be included on
    // the response
    $toilRequest2 = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => $type->id,
      'from_date' => $nextMonday->format('Ymd'),
      'duration' => 10,
      'expiry_date' => $nextMonday->modify('+5 days')->format('Ymd')
    ]);

    $result = civicrm_api3('TOILRequest', 'get');
    $this->assertEquals(2, $result['count']);
    $this->assertNotEmpty($result['values'][$toilRequest1->id]);
    $this->assertNotEmpty($result['values'][$toilRequest2->id]);
  }

  public function testGetWitTheExpiredParamReturnsOnlyExpiredRequests() {
    $contact = ContactFabricator::fabricate();

    $type = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 10
    ]);

    // This request has expired, and it will be included in
    // the response
    $toilRequest1 = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => $type->id,
      'from_date' => '20160101',
      'duration' => 10,
      'expiry_date' => '20160110'
    ]);

    $toilRequestBalanceChange = $this->findToilRequestBalanceChange($toilRequest1->id);
    LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $toilRequestBalanceChange->source_id,
      'source_type' => $toilRequestBalanceChange->source_type,
      'amount' => $toilRequestBalanceChange->amount * -1,
      'expiry_date' => CRM_Utils_Date::processDate($toilRequestBalanceChange->expiry_date),
      'expired_balance_change_id' => $toilRequestBalanceChange->id,
      'type_id' => $this->getBalanceChangeTypeValue('Debit')
    ]);

    $nextMonday = new DateTime('next monday');

    // This is not expired yet and it will not be included on
    // the response
    $toilRequest2 = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => $type->id,
      'from_date' => $nextMonday->format('Ymd'),
      'duration' => 10,
      'expiry_date' => $nextMonday->modify('+5 days')->format('Ymd')
    ]);

    $result = civicrm_api3('TOILRequest', 'get', ['expired' => true]);
    $this->assertEquals(1, $result['count']);
    $this->assertNotEmpty($result['values'][$toilRequest1->id]);
  }

  public function testGetWitTheExpiredParamDoesNotReturnsARequestWithAnExpiryDateInThePastButWithoutAnExpiredAmount() {
    $contact = ContactFabricator::fabricate();

    $type = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 10
    ]);

    // This request has expired, and it will be included in
    // the response
    $toilRequest1 = TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => $type->id,
      'from_date' => '20160101',
      'duration' => 10,
      'expiry_date' => '20160110'
    ]);

    $result = civicrm_api3('TOILRequest', 'get', ['expired' => true]);
    // The expiry date is in the past and the expired leave balance change was
    // not created yet. Nothing will be returned
    $this->assertEquals(0, $result['count']);

    $toilRequestBalanceChange = $this->findToilRequestBalanceChange($toilRequest1->id);
    // Now we create the expired balance change, but the amount will be 0, meaning
    // that, in practice, nothing has expired and all the accrued days were used
    LeaveBalanceChangeFabricator::fabricate([
      'source_id' => $toilRequestBalanceChange->source_id,
      'source_type' => $toilRequestBalanceChange->source_type,
      'amount' => 0,
      'expiry_date' => CRM_Utils_Date::processDate($toilRequestBalanceChange->expiry_date),
      'expired_balance_change_id' => $toilRequestBalanceChange->id,
      'type_id' => $this->getBalanceChangeTypeValue('Debit')
    ]);

    $result = civicrm_api3('TOILRequest', 'get', ['expired' => true]);
    // Even with the balance change, it should still return nothing because 0
    // means nothing has expired
    $this->assertEquals(0, $result['count']);
  }
}
