<?php

use CRM_HRLeaveAndAbsences_BAO_SicknessRequest as SicknessRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_SicknessRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_SicknessRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_SicknessRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    $this->requiredDocumentOptions = $this->getSicknessRequestRequiredDocumentsOptions();
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
    $this->sicknessRequestReasons = $this->getSicknessRequestReasons();
    $this->leaveRequestStatuses = $this->getLeaveRequestStatuses();

    $this->leaveContact = 1;
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];
  }

  public function testCreateSicknessRequest() {
    $contactID = 1;
    $fromDate = new DateTime("2016-11-14");
    $toDate = new DateTime("2016-11-17");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];

    //four working days which will create a balance change of 4
    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));

    $sicknessRequest = SicknessRequest::create([
      'type_id' => 1,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Appointment'],
      'required_documents' => $this->requiredDocumentOptions['Self certification form required']['value']. ',' . $this->requiredDocumentOptions['Back to work interview required']['value'],
    ], false);

    $leaveRequest = new LeaveRequest;
    $leaveRequest->id = $sicknessRequest->leave_request_id;
    $leaveRequest->find(true);
    $dates = $leaveRequest->getDates();
    $this->assertCount(4, $dates);
    $this->assertEquals('2016-11-14', $dates[0]->date);
    $this->assertEquals('2016-11-15', $dates[1]->date);
    $this->assertEquals('2016-11-16', $dates[2]->date);
    $this->assertEquals('2016-11-17', $dates[3]->date);

    $this->assertInstanceOf(SicknessRequest::class, $sicknessRequest);
    $this->assertEquals($sicknessRequest->reason, $sicknessReasons['Appointment']);
  }

  public function testUpdateSicknessRequestDoesNotCreateDuplicates() {
    $contactID = 1;
    $fromDate1 = new DateTime("2016-11-14");
    $toDate1 = new DateTime("2016-11-17");
    $fromDate2 = new DateTime("2016-11-16");
    $toDate2 = new DateTime("2016-11-18");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];
    $requiredDocuments1 = $this->requiredDocumentOptions['Self certification form required']['value']. ',' . $this->requiredDocumentOptions['Back to work interview required']['value'];
    $requiredDocuments2 = $this->requiredDocumentOptions['Self certification form required']['value'];

    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));

    $sicknessRequest1 = SicknessRequest::create([
      'type_id' => 1,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Appointment'],
      'required_documents' => $requiredDocuments1
    ], false);

    //Update the Sickness Request and leave request
    $sicknessRequest2 = SicknessRequest::create([
      'id' => $sicknessRequest1->id,
      'type_id' => 1,
      'contact_id' => $contactID,
      'status_id' => 2,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Accident'],
      'required_documents' => $requiredDocuments2,
    ], false);
    $leaveRequestID1 = $sicknessRequest1->leave_request_id;
    $leaveRequestID2 = $sicknessRequest2->leave_request_id;

    //confirm that SicknessRequest Still maintain Initial LeaveRequestID it had when created
    $this->assertEquals($leaveRequestID1, $leaveRequestID2);
    //confirm Its the same SicknessRequest and not a duplicate
    $this->assertEquals($sicknessRequest1->id, $sicknessRequest2->id);

    //confirm that contact has just one leave request in DB
    $leaveRequestObject = new LeaveRequest;
    $leaveRequestObject->contact_id = $contactID;
    $leaveRequestObject->find();
    $this->assertEquals(1, $leaveRequestObject->N);

    //confirm leave request was updated
    $leaveRequestObject->fetch();
    $this->assertEquals($fromDate2->format('Y-m-d'), $leaveRequestObject->from_date);
    $this->assertEquals($toDate2->format('Y-m-d'), $leaveRequestObject->to_date);
    $this->assertEquals(2, $leaveRequestObject->status_id);

    //confirm sickness request is updated
    $this->assertEquals($sicknessReasons['Appointment'], $sicknessRequest1->reason);
    $this->assertEquals($sicknessReasons['Accident'], $sicknessRequest2->reason);

    $this->assertEquals($requiredDocuments1, $sicknessRequest1->required_documents);
    $this->assertEquals($requiredDocuments2, $sicknessRequest2->required_documents);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidSicknessRequestException
   * @expectedExceptionMessage Sickness Requests should have a reason
   */
  public function testValidateSicknessRequestWhenReasonIsEmpty() {
    $fromDate = new DateTime("2016-11-14");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $requiredDocuments = $this->requiredDocumentOptions['Self certification form required']['value'];

    SicknessRequest::validateParams([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'required_documents' => $requiredDocuments
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidSicknessRequestException
   * @expectedExceptionMessage Sickness Requests should have a reason
   */
  public function testValidateParamsIsCalledOnCreate() {
    $fromDate = new DateTime("2016-11-14");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $requiredDocuments = $this->requiredDocumentOptions['Self certification form required']['value'];

    SicknessRequest::create([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'required_documents' => $requiredDocuments
    ], true);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidSicknessRequestException
   * @expectedExceptionMessage This absence does not allow sickness request
   */
  public function testValidateSicknessRequestWhenAbsenceTypeDoesNotAllowSicknessRequest() {
    $fromDate = new DateTime("2016-11-14");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $requiredDocuments = $this->requiredDocumentOptions['Self certification form required']['value'];

    $absenceType = AbsenceTypeFabricator::fabricate([
      'is_sick' => 0
    ]);

    SicknessRequest::validateParams([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'reason' => $this->sicknessRequestReasons['Appointment'],
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'required_documents' => $requiredDocuments
    ]);
  }

  /**
   * @expectedException \CRM_HRLeaveAndAbsences_Exception_InvalidLeaveRequestException
   * @expectedExceptionMessage The type of From Date should not be empty
   */
  public function testValidateParamsCallsLeaveRequestValidateParams() {
    $fromDate = new DateTime("2016-11-14");
    $requiredDocuments = $this->requiredDocumentOptions['Self certification form required']['value'];

    $absenceType = AbsenceTypeFabricator::fabricate([
      'is_sick' => 1
    ]);

    SicknessRequest::validateParams([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'reason' => $this->sicknessRequestReasons['Appointment'],
      'from_date' => $fromDate->format('YmdHis'),
      'required_documents' => $requiredDocuments
    ]);
  }

  public function testCreateAndUpdateSicknessRequest() {
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
      'from_date' => $startDate->format('YmdHis'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => $startDate->format('YmdHis'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'reason' => $this->sicknessRequestReasons['Accident']['value'],
      'required_documents' => $this->requiredDocumentOptions['Self certification form required']['value'],
    ];

    $sicknessRequest = SicknessRequest::create($params);

    $this->assertInstanceOf(SicknessRequest::class, $sicknessRequest);
    $this->assertEquals($sicknessRequest->reason, $params ['reason']);
    $this->assertEquals($sicknessRequest->required_documents, $params ['required_documents']);

    //update the Sickness Request
    $params['id'] = $sicknessRequest->id;
    $params['required_documents'] = $this->requiredDocumentOptions['Back to work interview required']['value'];
    $params['reason'] = $this->sicknessRequestReasons['Appointment']['value'];

    $sicknessRequest2 = SicknessRequest::create($params);

    $this->assertInstanceOf(SicknessRequest::class, $sicknessRequest2);
    $this->assertEquals($sicknessRequest2->reason, $params ['reason']);
    $this->assertEquals($sicknessRequest2->required_documents, $params ['required_documents']);
  }
}
