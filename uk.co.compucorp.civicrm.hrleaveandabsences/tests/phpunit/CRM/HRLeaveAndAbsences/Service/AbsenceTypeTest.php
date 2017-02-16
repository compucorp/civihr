<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest as TOILRequestFabricator;
use CRM_HRLeaveAndAbsences_Service_AbsenceType as AbsenceTypeService;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_AbsenceTypeTest extends BaseHeadlessTest {

  public function testPostUpdateActionDeletesTOILRequestsWhenTOILGetsDisabled() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 1,
    ]);

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 days'),
      'end_date' => CRM_Utils_Date::processDate('+ 300days'),
    ]);

    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+3 days'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ], true);

    $toilRequest2 = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('2016-12-10')
    ], true);

    //assert the records exist first before updating absence type
    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 2);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 2);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 2);

    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 2);

    //disable TOIL
    $updatedAbsenceType = AbsenceType::create([
      'id' => $absenceType->id,
      'allow_accruals_request' => false,
      'color' => '#000000'
    ]);

    $absenceTypeService = new AbsenceTypeService();
    $absenceTypeService->postUpdateActions($updatedAbsenceType);

    //confirm the balance change for the expired TOIL balance was not deleted
    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 1);
    $balanceChanges->fetch();
    $this->assertEquals($balanceChanges->source_id, $toilRequest2->id);

    //confirm the leave request for the expired TOIL balance was not deleted
    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 1);
    $leaveRequest->fetch();
    $this->assertEquals($leaveRequest->id, $toilRequest2->leave_request_id);

    //confirm the leave request dates for the expired TOIL balance was not deleted
    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 1);
    $leaveRequestDate->fetch();
    $this->assertEquals($leaveRequestDate->date, '2016-03-10');

    //confirm the TOIL Request for the expired TOIL balance was not deleted
    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 1);
    $toilRequest->fetch();
    $this->assertEquals($toilRequest->id, $toilRequest2->id);
  }
}
