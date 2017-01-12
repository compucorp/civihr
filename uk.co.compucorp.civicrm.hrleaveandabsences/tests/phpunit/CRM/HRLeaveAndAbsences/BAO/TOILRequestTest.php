<?php

use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest as TOILRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate as LeaveRequestDate;

/**
 * Class CRM_HRLeaveAndAbsences_BAO_TOILRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_TOILRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_TOILRequestHelpersTrait;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    $this->toilAmounts = $this->toilAmountOptions();
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   * @expectedExceptionMessage The TOIL duration cannot be empty
   */
  public function testValidateTOILRequestWhenDurationIsNotPresent() {
    TOILRequest::validateParams([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'toil_to_accrue' => 1,
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   * @expectedExceptionMessage The TOIL duration cannot be empty
   */
  public function testValidateTOILRequestWhenDurationIsEmpty() {
    TOILRequest::validateParams([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'toil_to_accrue' => 1,
      'duration' => null
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   * @expectedExceptionMessage The TOIL amount cannot be empty
   */
  public function testValidateTOILRequestWhenToilAmountIsNotPresent() {
    TOILRequest::validateParams([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'duration' => 120
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   * @expectedExceptionMessage The TOIL amount cannot be empty
   */
  public function testValidateTOILRequestWhenToilAmountIsEmpty() {
    TOILRequest::validateParams([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'duration' => 120,
      'toil_to_accrue' => null
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   * @expectedExceptionMessage The TOIL amount is not valid
   */
  public function testValidateTOILRequestWhenToilAmountIsNotValid() {
    TOILRequest::validateParams([
      'type_id' => 1,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'toil_to_accrue' => 200,
      'duration' => 120
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   * @expectedExceptionMessage The TOIL amount requested for is greater than the maximum for this Absence Type
   */
  public function testValidateTOILRequestWhenToilAmountIsGreaterThanMaximumAllowed() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 1,
      'is_active' => 1,
    ]);

    TOILRequest::validateParams([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'to_date' => '2016-11-18',
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   * @expectedExceptionMessage You cannot request TOIL for past days
   */
  public function testValidateTOILRequestWithPastDatesAndAbsenceTypeDoesNotAllow() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
      'allow_accrue_in_the_past' => false
    ]);

    TOILRequest::validateParams([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'to_date' => '2016-11-18',
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ]);
  }

  /**
   * @expectedException CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException
   * @expectedExceptionMessage You cannot request TOIL for past days
   */
  public function testValidateParamsIsCalledOnCreate() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
      'allow_accrue_in_the_past' => false
    ]);

    TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => '2016-11-14',
      'to_date' => '2016-11-18',
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ], true);
  }

  public function testCreateTOILRequestCreatesLeaveRequest() {

    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
    ]);

    $toilRequest = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'from_date_type' => 1,
      'to_date_type' => 1,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ], false);

    $leaveRequest = new LeaveRequest;
    $leaveRequest->id = $toilRequest->leave_request_id;
    $leaveRequest->find(true);

    $dates = $leaveRequest->getDates();
    $this->assertCount(4, $dates);
    $this->assertEquals($fromDate->format('Y-m-d'), $dates[0]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+1 day')), $dates[1]->date);
    $this->assertEquals(date('Y-m-d', strtotime('+2 days')), $dates[2]->date);
    $this->assertEquals($toDate->format('Y-m-d'), $dates[3]->date);

    $this->assertInstanceOf(TOILRequest::class, $toilRequest);
  }

  public function testCreateTOILRequestDoesNotCreateDuplicateLeaveRequestsWhenUpdated() {
    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');
    $toDate2 = new DateTime('+5 days');
    $contactID = 1;

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
    ]);

    $toilRequest1 = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ], false);

    //update TOIL
    $toilRequest2 = TOILRequest::create([
      'id' => $toilRequest1->id,
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 80
    ], false);

    $leaveRequestID1 = $toilRequest1->leave_request_id;
    $leaveRequestID2 = $toilRequest2->leave_request_id;

    //confirm that the TOILRequest Still maintain Initial LeaveRequestID it had when created
    $this->assertEquals($leaveRequestID1, $leaveRequestID2);
    //confirm Its the same TOILRequest and not a duplicate
    $this->assertEquals($toilRequest1->id, $toilRequest2->id);

    //confirm that contact has just one leave request in DB
    $leaveRequestObject = new LeaveRequest;
    $leaveRequestObject->contact_id = $contactID;
    $leaveRequestObject->find();
    $this->assertEquals(1, $leaveRequestObject->N);

    //confirm leave request was updated
    $leaveRequestObject->fetch();
    $this->assertEquals($toDate2->format('Y-m-d'), $leaveRequestObject->to_date);

    //confirm the TOILRequest is updated
    $this->assertEquals(80, $toilRequest2->duration);
  }

  public function testCreateTOILRequestDoesNotCreateDuplicateBalanceChange() {
    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
    ]);

    $toilRequest = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ], false);

    $toilBalanceChange = new LeaveBalanceChange();
    $toilBalanceChange->source_id = $toilRequest->id;
    $toilBalanceChange->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $toilBalanceChange->find();
    //No duplicates
    $this->assertEquals(1, $toilBalanceChange->N);

    //verify the balance change
    $toilBalanceChange->fetch();
    $this->assertEquals($this->toilAmounts['2 Days']['value'], $toilBalanceChange->amount);
  }

  public function testCreateTOILRequestBalanceChangeWhenTOILRequestIsUpdated() {
    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');
    $toDate2 = new DateTime('+5 days');
    $contactID = 1;

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
    ]);

    $toilRequest1 = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ], false);

    //update TOIL
    $toilRequest2 = TOILRequest::create([
      'id' => $toilRequest1->id,
      'type_id' => $absenceType->id,
      'contact_id' => $contactID,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate2->format('YmdHis'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => $this->toilAmounts['3 Days']['value'],
      'duration' => 80
    ], false);

    $toilBalanceChange = new LeaveBalanceChange();
    $toilBalanceChange->source_id = $toilRequest2->id;
    $toilBalanceChange->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $toilBalanceChange->find();
    //No duplicates
    $this->assertEquals(1, $toilBalanceChange->N);

    $toilBalanceChange->fetch();
    $this->assertEquals($this->toilAmounts['3 Days']['value'], $toilBalanceChange->amount);
  }

  public function testCreateTOILRequestBalanceChangeWhenNoExpiryDateIsGivenAndAbsenceTypeSaysTOILNeverExpires() {
    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
      'is_active' => 1,
    ]);

    $toilRequest = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ], false);

    $toilBalanceChange = new LeaveBalanceChange();
    $toilBalanceChange->source_id = $toilRequest->id;
    $toilBalanceChange->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $toilBalanceChange->find(true);

    //since Absence Type accrual does not expire
    $toilBalanceChange->fetch();
    $this->assertNull($toilBalanceChange->expiry_date);
  }

  public function testCreateTOILRequestBalanceChangeWhenNoExpiryDateIsGivenAndAbsenceTypeHasTOILExpiryDuration() {
    $fromDate = new DateTime();
    $toDate = new DateTime('+3 days');

    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'max_leave_accrual' => 10,
      'allow_accruals_request' => true,
      'accrual_expiration_duration' => 10,
      'accrual_expiration_unit' => AbsenceType::EXPIRATION_UNIT_DAYS,
      'is_active' => 1,
    ]);

    $toilRequest = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => $fromDate->format('YmdHis'),
      'to_date' => $toDate->format('YmdHis'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 120
    ], false);

    $expectedExpiryDate = new DateTime('+10 days');

    $toilBalanceChange = new LeaveBalanceChange();
    $toilBalanceChange->source_id = $toilRequest->id;
    $toilBalanceChange->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $toilBalanceChange->find(true);

    $this->assertEquals($toilBalanceChange->expiry_date, $expectedExpiryDate->format('Y-m-d'));
  }

  public function testCreateTOILRequestBalanceChangeWhenATOILExpiryDateIsGiven() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'title' => 'Title 1',
      'max_leave_accrual' => 10,
      'allow_accruals_request' => true,
      'accrual_expiration_duration' => 10,
      'accrual_expiration_unit' => AbsenceType::EXPIRATION_UNIT_DAYS,
      'is_active' => 1,
    ]);

    $expiryDate = new DateTime('+100 days');

    $toilRequest = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => date('YmdHis'),
      'to_date' => date('YmdHis'),
      'to_date_type' => 1,
      'from_date_type' => 1,
      'toil_to_accrue' => $this->toilAmounts['2 Days']['value'],
      'duration' => 300,
      'expiry_date' => $expiryDate->format('Ymd')
    ], false);

    $toilBalanceChange = new LeaveBalanceChange();
    $toilBalanceChange->source_id = $toilRequest->id;
    $toilBalanceChange->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $toilBalanceChange->find(true);

    // The settings on the AbsenceType says TOIL Requests should expire in 10 days,
    // but the expiry date passed to create was 100 days, so that should be the
    // date used
    $this->assertEquals($expiryDate->format('Y-m-d'), $toilBalanceChange->expiry_date);
  }

  public function testOpenToilRequestWillNotBeUpdatedIfRequestedAmountIsMoreThanMaxLeaveAccrual() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'max_leave_accrual' => 3,
      'allow_accruals_request' => true,
      'is_active' => 1,
    ]);

    $toilRequest = TOILRequest::create([
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 3,
      'from_date' => date('YmdHis'),
      'to_date' => date('YmdHis'),
      'toil_to_accrue' => $this->toilAmounts['3 Days']['value'],
      'duration' => 300,
    ], false);

    // decrease the max leave accrual
    AbsenceType::create([
      'id' => $absenceType->id,
      'max_leave_accrual' => 1,
      'allow_accruals_request' => true,
      'color' => '#000000'
    ]);

    $this->setExpectedException('CRM_HRLeaveAndAbsences_Exception_InvalidTOILRequestException', 'The TOIL amount requested for is greater than the maximum for this Absence Type');

    // update the TOIL request status
    TOILRequest::validateParams([
      'id' => $toilRequest->id,
      'type_id' => $absenceType->id,
      'contact_id' => 1,
      'status_id' => 1,
      'from_date' => date('YmdHis'),
      'to_date' => date('YmdHis'),
      'toil_to_accrue' => $this->toilAmounts['3 Days']['value'],
      'duration' => 300,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ]);
  }

  public function testDeleteForAbsenceTypeWithinAGivenDateRangeWhenNonExpiredOnlyFlagIsFalse() {
    $absenceType = 1;
    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-13'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-04-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-04-12'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('2016-12-10')
    ]);

    $toilRequest3 = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2015-03-10'),
      'to_date' => CRM_Utils_Date::processDate('2015-03-11'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('2015-10-03')
    ]);

    //assert the records exist first before deletion
    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 3);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 3);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 9);

    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 3);

    //The two balance changes within the given period will be deleted
    //the two leave requests and the TOIL requests within the given period will be deleted
    //the leave request dates within the given period will be deleted
    $startDate = new DateTime('2016-01-01');
    $endDate = new DateTime('2016-12-31');
    TOILRequest::deleteAllForAbsenceType($absenceType, $startDate, $endDate);

    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 1);
    $balanceChanges->fetch();

    //this balance change is not within the given period
    $this->assertEquals($balanceChanges->source_id, $toilRequest3->id);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 1);
    $leaveRequest->fetch();

    //this leave request is not within the given period
    $this->assertEquals($leaveRequest->id, $toilRequest3->leave_request_id);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 2);
    $leaveRequestDate->fetch();

    //these leave request dates are not within the given period
    $this->assertEquals($leaveRequestDate->date, '2015-03-10');
    $leaveRequestDate->fetch();
    $this->assertEquals($leaveRequestDate->date, '2015-03-11');

    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 1);
    $toilRequest->fetch();

    //this toil request is not within the given period
    $this->assertEquals($toilRequest->id, $toilRequest3->id);
  }

  public function testDeleteForAbsenceTypeWithinAGivenDateRangeWhenNonExpiredOnlyFlagIsTrue() {
    $absenceType = 1;
    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-13'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    $toilRequest2 = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('2016-12-10')
    ]);

    $toilRequest3 = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-04-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-04-10'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('2016-10-03')
    ]);

    //assert the records exist first before deletion
    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 3);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 3);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 6);

    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 3);

    //Only the first toil request is valid and not expired, so will be deleted
    $startDate = new DateTime('2016-01-01');
    $endDate = new DateTime('2016-12-31');
    TOILRequest::deleteAllForAbsenceType($absenceType, $startDate, $endDate, true);

    //the expired balance changes for the other two TOILS will not be deleted
    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 2);
    $balanceChanges->fetch();

    $this->assertEquals($balanceChanges->source_id, $toilRequest2->id);
    $balanceChanges->fetch();
    $this->assertEquals($balanceChanges->source_id, $toilRequest3->id);

    //the leave requests for the other two TOILS linked to expired balance changes will not be deleted
    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 2);
    $leaveRequest->fetch();

    $this->assertEquals($leaveRequest->id, $toilRequest2->leave_request_id);
    $leaveRequest->fetch();
    $this->assertEquals($leaveRequest->id, $toilRequest3->leave_request_id);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 2);
    $leaveRequestDate->fetch();

    //these leave request dates are for the other two TOILS linked to expired balance changes will not be deleted
    $this->assertEquals($leaveRequestDate->date, '2016-03-10');
    $leaveRequestDate->fetch();
    $this->assertEquals($leaveRequestDate->date, '2016-04-10');

    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 2);
    $toilRequest->fetch();
    $this->assertEquals($toilRequest->id, $toilRequest2->id);
    $toilRequest->fetch();
    $this->assertEquals($toilRequest->id, $toilRequest3->id);
  }

  public function testDeleteForAbsenceTypeWithNoEndDateWhenNonExpiredOnlyFlagIsTrue() {
    $absenceType = 1;
    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-13'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    $toilRequest2 = TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-04-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-04-10'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('2016-12-10')
    ]);

    //assert the records exist first before deletion
    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 2);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 2);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 5);

    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 2);

    //Only the first toil request is valid and not expired, so will be deleted
    $startDate = new DateTime('2016-03-10');
    TOILRequest::deleteAllForAbsenceType($absenceType, $startDate, null, true);

    //the expired balance change for the other TOIL will not be deleted
    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 1);
    $balanceChanges->fetch();

    $this->assertEquals($balanceChanges->source_id, $toilRequest2->id);

    //the leave request for the other TOIL linked to expired balance changes will not be deleted
    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 1);
    $leaveRequest->fetch();

    $this->assertEquals($leaveRequest->id, $toilRequest2->leave_request_id);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 1);
    $leaveRequestDate->fetch();

    //the leave request date for the other TOIL linked to expired balance changes will not be deleted
    $this->assertEquals($leaveRequestDate->date, '2016-04-10');

    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 1);
    $toilRequest->fetch();
    $this->assertEquals($toilRequest->id, $toilRequest2->id);
  }

  public function testDeleteForAbsenceTypeWithNoEndDateWhenNonExpiredOnlyFlagIsFalse() {
    $absenceType = 1;
    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-03-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-03-13'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('+100 days')
    ]);

    TOILRequestFabricator::fabricateWithoutValidation([
      'type_id' => $absenceType,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-04-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-04-10'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('2016-12-10')
    ]);

    //assert the records exist first before deletion
    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 2);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 2);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 5);

    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 2);

    $startDate = new DateTime('2016-03-10');
    TOILRequest::deleteAllForAbsenceType($absenceType, $startDate, null, false);

    $balanceChanges = new LeaveBalanceChange();
    $balanceChanges->find();
    $this->assertEquals($balanceChanges->N, 0);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->find();
    $this->assertEquals($leaveRequest->N, 0);

    $leaveRequestDate = new LeaveRequestDate();
    $leaveRequestDate->find();
    $this->assertEquals($leaveRequestDate->N, 0);

    $toilRequest = new ToilRequest();
    $toilRequest->find();
    $this->assertEquals($toilRequest->N, 0);
  }
}
