<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_SicknessRequest as SicknessRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;

use CRM_HRLeaveAndAbsences_BAO_SicknessRequest as SicknessRequest;

/**
 * Class api_v3_TOILRequestTest
 *
 * @group headless
 */
class SicknessRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_SicknessRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $this->requiredDocumentOptions = $this->getSicknessRequestRequiredDocumentsOptions();
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
    $this->leaveRequestStatuses = $this->getLeaveRequestStatuses();
    $this->sicknessRequestReasons = $this->getSicknessRequestReasons();

    $this->leaveContact = 1;
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];
  }

  public function testSicknessRequestIsValidReturnsErrorWhenReasonIsEmpty() {
    $fromDate = new DateTime('2016-11-14');
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $requiredDocuments = $this->requiredDocumentOptions['Self certification form required']['value'];

    $result = civicrm_api3('SicknessRequest', 'isvalid', [
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'required_documents' => $requiredDocuments
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'reason' => ['sickness_request_empty_reason']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testSicknessRequestIsValidReturnsErrorWhenAbsenceTypeDoesNotAllowSicknessRequest() {
    $fromDate = new DateTime('2016-11-14');
    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $requiredDocuments = $this->requiredDocumentOptions['Self certification form required']['value'];

    $absenceType = AbsenceTypeFabricator::fabricate([
      'is_sick' => 0
    ]);

    $result = civicrm_api3('SicknessRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'reason' => $this->sicknessRequestReasons['Appointment'],
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'required_documents' => $requiredDocuments
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'type_id' => ['sickness_request_absence_type_does_not_allow_sickness_request']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testSicknessRequestIsValidReturnsLeaveRequestTypeErrorWhenLeaveRequestValidationFails() {
    $fromDate = new DateTime("2016-11-14");
    $requiredDocuments = $this->requiredDocumentOptions['Self certification form required']['value'];

    $absenceType = AbsenceTypeFabricator::fabricate([
      'is_sick' => 1
    ]);

    $result = civicrm_api3('SicknessRequest', 'isvalid', [
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'reason' => $this->sicknessRequestReasons['Appointment'],
      'from_date' => $fromDate->format('YmdHis'),
      'required_documents' => $requiredDocuments
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => [
        'from_date_type' => ['leave_request_empty_from_date_type']
      ]
    ];
    $this->assertEquals($expectedResult, $result);
  }

  public function testSicknessRequestIsValidShouldNotReturnErrorWhenValidationsPass() {
    $contact = ContactFabricator::fabricate();

    $startDate = new DateTime();
    $endDate = new DateTime('+5 days');

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => $startDate->format('YmdHis'),
      'end_date' => $endDate->format('YmdHis'),
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => $startDate->format('Y-m-d')]
    );

    $absenceType = AbsenceTypeFabricator::fabricate([
      'is_sick' => 1
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $contact['id'],
      'period_id' => $period->id,
      'type_id' => $absenceType->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 20);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));

    $result = civicrm_api3('SicknessRequest', 'isvalid', [
      'contact_id' => $contact['id'],
      'type_id' => $absenceType->id,
      'from_date' => $startDate->format('Y-m-d'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $endDate->format('Y-m-d'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'status_id' => 1,
      'reason' => $sicknessReasons['Accident'],
      'sequential' => 1,
    ]);

    $expectedResult = [
      'is_error' => 0,
      'version' => 3,
      'count' => 0,
      'values' => []
    ];

    $this->assertEquals($expectedResult, $result);
  }

  public function testSicknessRequestGetShouldReturnAssociatedLeaveRequestData() {
    $fromDate = new DateTime('2016-11-14');
    $toDate = new DateTime('2016-11-17');

    $fromDate2 = new DateTime('2016-11-20');
    $toDate2 = new DateTime('2016-11-30');

    $fromType = $this->leaveRequestDayTypes['All Day']['value'];
    $toType = $this->leaveRequestDayTypes['All Day']['value'];

    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));

    $sicknessRequest1 = SicknessRequest::create([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Appointment'],
      'required_documents' => $this->requiredDocumentOptions['Self certification form required']['value'],
    ], false);

    $sicknessRequest2 = SicknessRequest::create([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Accident'],
      'required_documents' => $this->requiredDocumentOptions['Self certification form required']['value']. ',' . $this->requiredDocumentOptions['Back to work interview required']['value'],
    ], false);

    $expectedResult = [
      [
        'id' => $sicknessRequest1->id,
        'type_id' => 1,
        'contact_id' => 1,
        'status_id' => 1,
        'from_date' => $fromDate->format('Y-m-d'),
        'from_date_type' => $fromType,
        'to_date' => $toDate->format('Y-m-d'),
        'to_date_type' => $toType,
        'leave_request_id' => $sicknessRequest1->leave_request_id,
        'reason' => $sicknessRequest1->reason,
        'required_documents' => $sicknessRequest1->required_documents
      ],
      [
        'id' => $sicknessRequest2->id,
        'type_id' => 1,
        'contact_id' => 1,
        'status_id' => 1,
        'from_date' => $fromDate2->format('Y-m-d'),
        'from_date_type' => $fromType,
        'to_date' => $toDate2->format('Y-m-d'),
        'to_date_type' => $toType,
        'leave_request_id' => $sicknessRequest2->leave_request_id,
        'reason' => $sicknessRequest2->reason,
        'required_documents' => $sicknessRequest2->required_documents
      ]
    ];

    $result = civicrm_api3('SicknessRequest', 'get', ['contact_id'=> 1, 'sequential' => 1]);
    $this->assertEquals($expectedResult, $result['values']);
  }

  public function testCreateAlsoCreatesTheBalanceChangesForTheSicknessRequests() {
    $startDate = new DateTime();
    $endDate = new DateTime('+5 days');

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => $startDate->format('YmdHis'),
      'end_date' => $endDate->format('YmdHis'),
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => $startDate->format('Y-m-d')]
    );

    $absenceType = AbsenceTypeFabricator::fabricate([
      'is_sick' => true
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $this->leaveContact,
      'period_id' => $period->id,
      'type_id' => $absenceType->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 20);

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));

    $result = civicrm_api3('SicknessRequest', 'create', [
      'contact_id' => $this->leaveContact,
      'type_id' => $absenceType->id,
      'from_date' => $startDate->format('Y-m-d'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $endDate->format('Y-m-d'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'status_id' => 3,
      'reason' => $sicknessReasons['Accident'],
      'sequential' => 1,
    ]);

    $leaveRequest = LeaveRequest::findById($result['values'][0]['leave_request_id']);
    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(6, $balanceChanges);
  }

  public function testDeleteAlsoDeletesLeaveRequestAndItsBalanceChangesFor() {
    $contact = ContactFabricator::fabricate();

    $startDate = new DateTime();
    $endDate = new DateTime('+5 days');

    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));

    $sicknessRequest = SicknessRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $contact['id'],
      'type_id' => 1,
      'from_date' => $startDate->format('Ymd'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $endDate->format('Ymd'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'status_id' => 1,
      'reason' => $sicknessReasons['Accident'],
      'sequential' => 1,
    ], true);

    $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);
    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(6, $balanceChanges);

    civicrm_api3('SicknessRequest', 'delete', ['id' => $sicknessRequest->id]);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $this->assertCount(0, $balanceChanges);

    try {
      $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);
    } catch(Exception $e) {
      return;
    }

    $this->fail("Expected to not find the LeaveRequest with {$leaveRequest->id}, but it was found");
  }

  public function testCreateAndUpdateResponseIncludesSicknessRequestAndLeaveRequestRelatedFields() {
    $startDate = new DateTime('next monday');
    $endDate = new DateTime('+10 days');

    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => $startDate->format('Y-m-d')]
    );

    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => $startDate->format('YmdHis'),
      'end_date' => $endDate->format('YmdHis')
    ]);

    $type = AbsenceTypeFabricator::fabricate([
      'is_sick' => true
    ]);

    $leavePeriodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $this->leaveContact,
      'period_id' => $period->id,
      'type_id' => $type->id,
    ]);

    $this->createLeaveBalanceChange($leavePeriodEntitlement->id, 10);

    $params = [
      'contact_id' => $this->leaveContact,
      'type_id' => $type->id,
      'status_id' => $this->leaveRequestStatuses['Waiting Approval']['value'],
      'from_date' => $startDate->format('Y-m-d'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $startDate->format('Y-m-d'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'reason' => $this->sicknessRequestReasons['Accident']['value'],
      'required_documents' => $this->requiredDocumentOptions['Self certification form required']['value'],
      'sequential' => 1
    ];
    $result = civicrm_api3('SicknessRequest', 'create', $params);

    $params['id'] = $result['values'][0]['id'];
    $params['leave_request_id'] = $result['values'][0]['leave_request_id'];
    unset($params['sequential']);
    $expectedValues = $params;

    $this->assertEquals($expectedValues, $result['values'][0]);

    //update the sickness request and leave request
    $params['required_documents'] = $this->requiredDocumentOptions['Back to work interview required']['value'];
    $params['reason'] = $this->sicknessRequestReasons['Appointment']['value'];
    $params['from_date_type'] = $this->leaveRequestDayTypes['1/2 PM']['value'];
    $params['sequential'] = 1;

    $result2 = civicrm_api3('SicknessRequest', 'create', $params);
    unset($params['sequential']);
    $expectedValues = $params;
    $this->assertEquals($expectedValues, $result2['values'][0]);
  }
}
