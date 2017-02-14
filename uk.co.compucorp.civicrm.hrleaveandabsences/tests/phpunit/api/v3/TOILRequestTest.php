<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveBalanceChange as LeaveBalanceChangeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest as TOILRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;

/**
 * Class api_v3_TOILRequestTest
 *
 * @group headless
 */
class api_v3_TOILRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_TOILRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');

    $this->toilAmounts = $this->toilAmountOptions();
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();

    $this->leaveContact = 1;
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];
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
      'version' => 3,
      'count' => 1,
      'values' => [
        'toil_to_accrue' => ['toil_request_toil_amount_is_invalid']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testTOILRequestIsValidShouldReturnErrorWhenToilAmountIsGreaterThanMaximumAllowed() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date'   => CRM_Utils_Date::processDate('+20 days'),
    ]);

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
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'toil_to_accrue' => ['toil_request_toil_amount_more_than_maximum_for_absence_type']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testTOILRequestIsValidShouldReturnErrorWhenTOILRequestIsMadeWithPastDatesAndAbsenceTypeDoesNotAllowPastDates() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date'   => CRM_Utils_Date::processDate('+20 days'),
    ]);

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
      'from_date' => CRM_Utils_Date::processDate('-1 day'),
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'from_date' => ['toil_request_toil_cannot_be_requested_for_past_days']
      ]
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testTOILRequestIsValidShouldNotReturnErrorWhenValidationsPass() {

    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');
    $contactID = 1;

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date'   => CRM_Utils_Date::processDate('+20 days'),
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contactID],
      ['period_start_date' => $fromDate->format('Y-m-d')]
    );

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contactID,
      'period_id' => $period->id,
      'type_id' => $absenceType->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 20);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $result = civicrm_api3('TOILRequest', 'isvalid', [
      'contact_id' => $contactID,
      'from_date_type' => $fromType = $this->leaveRequestDayTypes['All Day']['id'],
      'to_date_type' => $fromType = $this->leaveRequestDayTypes['All Day']['id'],
      'type_id' => $absenceType->id,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 0,
      'values' => []
    ];

    $this->assertEquals($expectedResult, $result);
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

    $toilRequest1 = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => $toType,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 60
    ], true);

    $toilRequest2 = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => $toType,
      'toil_to_accrue' => $this->toilAmounts['3 Days']['value'],
      'duration' => 120
    ], true);

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

  public function testTOILRequestIsValidShouldReturnErrorWhenDurationIsEmpty() {
    $result = civicrm_api3('TOILRequest', 'isValid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'toil_to_accrue' => 200,
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'duration' => ['toil_request_duration_is_empty']
      ]
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testTOILRequestIsValidShouldReturnErrorWhenToilToAccrueIsEmpty() {
    $result = civicrm_api3('TOILRequest', 'isValid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'duration' => 10,
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'toil_to_accrue' => ['toil_request_toil_to_accrue_is_empty']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testTOILRequestIsValidShouldReturnErrorWhenAbsenceTypeDoesNotAllowAccrual() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => false,
    ]);

    $result = civicrm_api3('TOILRequest', 'isValid', [
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
      'version' => 3,
      'count' => 1,
      'values' => [
        'type_id' => ['toil_request_toil_accrual_not_allowed_for_absence_type']
      ]
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testTOILRequestIsValidReturnsLeaveRequestTypeErrorWhenLeaveRequestValidationFails() {
    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date'   => CRM_Utils_Date::processDate('+20 days'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
    ]);

    $result = civicrm_api3('TOILRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'status_id' => 1,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'from_date' => ['leave_request_empty_from_date']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testCreateResponseAlsoIncludeTheLeaveRequestFields() {
    $startDate = new DateTime('next monday');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => $startDate->format('Y-m-d')]
    );

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 day'),
      'end_date' => CRM_Utils_Date::processDate('+20 days')
    ]);

    $type = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 10
    ]);

    $leavePeriodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $this->leaveContact,
      'period_id' => $period->id,
      'type_id' => $type->id,
    ]);

    $this->createLeaveBalanceChange($leavePeriodEntitlement->id, 10);

    $result = civicrm_api3('TOILRequest', 'create', [
      'contact_id' => $this->leaveContact,
      'type_id' => $type->id,
      'status_id' => $this->getLeaveRequestStatuses()['Waiting Approval']['value'],
      'from_date' => $startDate->format('Y-m-d'),
      'to_date' => $startDate->format('Y-m-d'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'toil_to_accrue' => 3,
      'duration' => 60,
      'sequential' => 1
    ]);

    $expectedValues = [
      'contact_id' => $this->leaveContact,
      'type_id' => $type->id,
      'status_id' => $this->getLeaveRequestStatuses()['Waiting Approval']['value'],
      'from_date' => $startDate->format('Y-m-d'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => $startDate->format('Y-m-d'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
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
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'duration' => 10,
      'expiry_date' => '20160110'
    ], true);

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
      'to_date' => $nextMonday->format('Ymd'),
      'duration' => 10,
      'expiry_date' => $nextMonday->modify('+5 days')->format('Ymd')
    ], true);

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
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'duration' => 10,
      'expiry_date' => CRM_Utils_Date::processDate('2016-01-10')
    ], true);

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
    TOILRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => $type->id,
      'from_date' => $nextMonday->format('Ymd'),
      'to_date' => $nextMonday->format('Ymd'),
      'duration' => 10,
      'expiry_date' => $nextMonday->modify('+5 days')->format('Ymd')
    ], true);

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
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'duration' => 10,
      'expiry_date' => '20160110'
    ], true);

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
