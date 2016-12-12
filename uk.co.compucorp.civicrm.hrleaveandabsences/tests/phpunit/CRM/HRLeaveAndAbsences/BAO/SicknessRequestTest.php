<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_ContactWorkPattern as ContactWorkPatternFabricator;
use CRM_HRLeaveAndAbsences_BAO_SicknessRequest as SicknessRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_SicknessRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_SicknessRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_SicknessRequestHelpersTrait;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    $this->requiredDocumentOptions = $this->requiredDocumentOptionsBuilder();
    $this->leaveRequestDayTypes = $this->leaveRequestDayTypeOptionsBuilder();
  }

  public function testCreateSicknessRequest() {
    $contact = ContactFabricator::fabricate();
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);
    $periodStartDate = date('2016-01-01');
    $title = 'Job Title';

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
      'title' => $title
    ]);

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id
    ]);

    $fromDate = new DateTime("2016-11-14");
    $toDate = new DateTime("2016-11-17");
    $fromType = $this->leaveRequestDayTypes['All Day']['id'];
    $toType = $this->leaveRequestDayTypes['All Day']['id'];

    //four working days which will create a balance change of 4
    $sicknessReasons = array_flip(SicknessRequest::buildOptions('reason'));

    $sicknessRequest = SicknessRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Appointment'],
      'required_documents' => $this->requiredDocumentOptions['Self certification form required']['value']. ',' . $this->requiredDocumentOptions['Back to work interview required']['value'],
    ]);

    $this->assertInstanceOf(SicknessRequest::class, $sicknessRequest);
    $this->assertEquals($sicknessRequest->reason, $sicknessReasons['Appointment']);
  }

  public function testUpdateSicknessRequestDoesNotCreateDuplicates() {
    $contact = ContactFabricator::fabricate();
    $period = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'end_date'   => CRM_Utils_Date::processDate('2016-12-31'),
    ]);

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Type 1',
    ]);

    $periodEntitlement = LeavePeriodEntitlementFabricator::fabricate([
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'period_id' => $period->id
    ]);

    $this->createLeaveBalanceChange($periodEntitlement->id, 3);
    $periodStartDate = date('2016-01-01');
    $title = 'Job Title';

    HRJobContractFabricator::fabricate([
      'contact_id' => $contact['id']
    ],
    [
      'period_start_date' => $periodStartDate,
      'title' => $title
    ]);

    $workPattern = WorkPatternFabricator::fabricateWithA40HourWorkWeek();
    ContactWorkPatternFabricator::fabricate([
      'contact_id' => $contact['id'],
      'pattern_id' => $workPattern->id
    ]);

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
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => $fromDate1->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate1->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Appointment'],
      'required_documents' => $requiredDocuments1
    ]);

    //Update the Sickness Request and leave request
    $sicknessRequest2 = SicknessRequest::create([
      'id' => $sicknessRequest1->id,
      'type_id' => $absenceType->id,
      'contact_id' => $contact['id'],
      'status_id' => 2,
      'from_date' => $fromDate2->format('YmdHis'),
      'from_date_type' => $fromType,
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => $toType,
      'reason' => $sicknessReasons['Accident'],
      'required_documents' => $requiredDocuments2,
    ]);
    $leaveRequestID1 = $sicknessRequest1->leave_request_id;
    $leaveRequestID2 = $sicknessRequest2->leave_request_id;

    //confirm that SicknessRequest Still maintain Initial LeaveRequestID it had when created
    $this->assertEquals($leaveRequestID1, $leaveRequestID2);
    //confirm Its the same SicknessRequest and not a duplicate
    $this->assertEquals($sicknessRequest1->id, $sicknessRequest2->id);

    //confirm that contact has just one leave request in DB
    $leaveRequestObject = new LeaveRequest;
    $leaveRequestObject->contact_id = $contact['id'];
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
}
