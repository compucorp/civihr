<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Service_AbsenceType as AbsenceTypeService;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_AbsenceTypeTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  private $absenceTypeService;

  public function setUp() {
    $this->absenceTypeService = new AbsenceTypeService();
  }

  public function testPostUpdateActionDeletesTOILRequestsWhenTOILGetsDisabled() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 1,
    ]);

    AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-1 days'),
      'end_date' => CRM_Utils_Date::processDate('+ 300days'),
    ]);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('+3 days'),
      'to_date' => CRM_Utils_Date::processDate('+3 days'),
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('+100 days'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], true);

    $toilRequest2 = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'toil_expiry_date' => CRM_Utils_Date::processDate('2016-12-10'),
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
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

    //disable TOIL
    $updatedAbsenceType = AbsenceType::create([
      'id' => $absenceType->id,
      'allow_accruals_request' => false,
      'color' => '#000000'
    ]);

    $this->absenceTypeService->postUpdateActions($updatedAbsenceType);

    //confirm the balance change for the expired TOIL balance was not deleted
    $balanceChange = new LeaveBalanceChange();
    $balanceChange->find(true);
    $this->assertEquals($balanceChange->N, 1);
    $toilRequestBalanceChange = $this->findToilRequestMainBalanceChange($toilRequest2->id);
    $this->assertEquals($toilRequestBalanceChange->id, $balanceChange->id);

    //confirm the leave request for the expired TOIL balance was not deleted
    $leaveRequest = new LeaveRequest();
    $leaveRequest->find(true);
    $this->assertEquals($leaveRequest->N, 1);
    $this->assertEquals($leaveRequest->id, $toilRequest2->id);

    //confirm the leave request dates for the expired TOIL balance was not deleted
    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find(true);
    $this->assertEquals($leaveRequestDate->N, 1);
    $this->assertEquals($leaveRequestDate->date, '2016-03-10');
  }

  /**
   * @expectedException UnexpectedValueException
   * @expectedExceptionMessage Absence type cannot be deleted because it is linked to one or more leave requests
   */
  public function testDeleteThrowsExceptionWhenDeletingAnAbsenceTypeThatIsLinkedToALeaveRequest() {
    $absenceTypeID = 1;
    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => 1,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ], true);

    $this->absenceTypeService->delete($absenceTypeID);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_OperationNotAllowedException
   * @expectedExceptionMessage Reserved types cannot be deleted!
   */
  public function testDeleteThrowsExceptionWhenDeletingAnAbsenceTypeThatIsReserved() {
    $absenceTypeID = AbsenceTypeFabricator::createReservedType();
    $this->absenceTypeService->delete($absenceTypeID);
  }

  public function testDeleteCanDeleteAnAbsenceTypeThatIsNotUsedOrReserved() {
    $absenceType = AbsenceTypeFabricator::fabricate();
    $this->absenceTypeService->delete($absenceType->id);

    try {
      AbsenceType::findById($absenceType->id);
    } catch(Exception $e) {
      return;
    }
    $this->fail("Expected to not find the AbsenceType with {$absenceType->id}, but it was found");
  }

  public function testAbsenceTypeHasEverBeenUsedReturnsTrueWhenAbsenceTypeIsLinkedToLeaveRequest() {
    $absenceTypeID = 1;

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => 1,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ], true);

    LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceTypeID,
      'contact_id' => 1,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-02'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('2016-01-02'),
      'to_date_type' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ], true);

    $this->assertTrue($this->absenceTypeService->absenceTypeHasEverBeenUsed($absenceTypeID));
  }

  public function testAbsenceTypeHasEverBeenUsedReturnsFalseWhenAbsenceTypeIsNotLinkedToLeaveRequest() {
    $absenceTypeID = 1;
    $this->assertFalse($this->absenceTypeService->absenceTypeHasEverBeenUsed($absenceTypeID));
  }
}
