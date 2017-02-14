<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest as TOILRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveBalanceChangeTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;

  private $leaveBalanceChangeService;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $this->leaveBalanceChangeService = new LeaveBalanceChangeService();
  }

  public function testItCanCreateBalanceChangesForALeaveRequest() {
    $contact = ContactFabricator::fabricate();

    HRJobContractFabricator::fabricate(
      ['contact_id' => $contact['id']],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $leaveRequestDateTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    // a 9 days leave request, from friday to saturday of the next week
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => $contact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $leaveRequestDateTypes['all_day'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-09'),
      'to_date_type' => $leaveRequestDateTypes['all_day'],
    ]);

    $this->leaveBalanceChangeService->createForLeaveRequest($leaveRequest);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used and there are 3 weekend days on the
    // leave period (2 saturdays and 1 sunday), the balance change will be -6
    // (the working days of all the 9 days requested)
    $this->assertEquals(-6, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is -6, we must have 9 balance changes, one for
    // each date
    $this->assertCount(9, $balanceChanges);
  }

  public function testCreateForTOILRequestDoesNotCreateDuplicateBalanceChanges() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 1,
    ]);

    $params = $this->getDefaultTOILparams(['type_id' => $absenceType->id]);
    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation($params);

    $this->leaveBalanceChangeService->createForTOILRequest($toilRequest, $params['type_id'], $params['toil_to_accrue']);

    $toilBalanceChange = new LeaveBalanceChange();
    $toilBalanceChange->source_id = $toilRequest->id;
    $toilBalanceChange->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $toilBalanceChange->find();
    //No duplicates
    $this->assertEquals(1, $toilBalanceChange->N);

    //verify the balance change
    $toilBalanceChange->fetch();
    $this->assertEquals($params['toil_to_accrue'], $toilBalanceChange->amount);
  }

  public function testCreateForTOILRequestDoesNotCreateDuplicateBalanceChangeForAnUpdatedTOIL() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
    ]);

    $params = $this->getDefaultTOILParams(['type_id' => $absenceType->id]);
    $toilRequest1 = TOILRequestFabricator::fabricateWithoutValidation($params);
    $this->leaveBalanceChangeService->createForTOILRequest($toilRequest1, $params['type_id'], $params['toil_to_accrue']);

    $params['id'] = $toilRequest1->id;
    $params['toil_to_accrue'] = 3;
    //update TOIL
    $toilRequest2 = TOILRequestFabricator::fabricateWithoutValidation($params);
    $this->leaveBalanceChangeService->createForTOILRequest($toilRequest2, $params['type_id'], $params['toil_to_accrue']);

    $toilBalanceChange = new LeaveBalanceChange();
    $toilBalanceChange->source_id = $toilRequest2->id;
    $toilBalanceChange->source_type = LeaveBalanceChange::SOURCE_TOIL_REQUEST;
    $toilBalanceChange->find();
    //No duplicates
    $this->assertEquals(1, $toilBalanceChange->N);

    $toilBalanceChange->fetch();
    $this->assertEquals($params['toil_to_accrue'], $toilBalanceChange->amount);
  }

  public function testDeleteForTOILRequestDeletesTheTOILBalanceChange() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
    ]);

    $params = $this->getDefaultTOILParams(['type_id' => $absenceType->id]);
    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation($params);

    $this->leaveBalanceChangeService->createForTOILRequest($toilRequest, $params['type_id'], $params['toil_to_accrue']);

    //delete balance change for toil
    $this->leaveBalanceChangeService->deleteForTOILRequest($toilRequest);
    $this->assertNull($this->findToilRequestBalanceChange($toilRequest->id));
  }

  public function testCreateForTOILRequestWhenNoExpiryDateIsGivenAndAbsenceTypeSaysTOILNeverExpires() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 4,
    ]);

    $params = $this->getDefaultTOILParams(['type_id' => $absenceType->id]);
    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation($params);

    $this->leaveBalanceChangeService->createForTOILRequest($toilRequest, $params['type_id'], $params['toil_to_accrue']);

    $toilBalanceChange = $this->findToilRequestBalanceChange($toilRequest->id);
    $this->assertNull($toilBalanceChange->expiry_date);
  }

  public function testCreateForTOILRequestWhenNoExpiryDateIsGivenAndAbsenceTypeHasTOILExpiryDuration() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'max_leave_accrual' => 10,
      'allow_accruals_request' => true,
      'accrual_expiration_duration' => 10,
      'accrual_expiration_unit' => AbsenceType::EXPIRATION_UNIT_DAYS,
    ]);

    $params = $this->getDefaultTOILParams(['type_id' => $absenceType->id]);
    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation($params);

    $this->leaveBalanceChangeService->createForTOILRequest($toilRequest, $params['type_id'], $params['toil_to_accrue']);

    $expectedExpiryDate = new DateTime('+10 days');

    $toilBalanceChange = $this->findToilRequestBalanceChange($toilRequest->id);

    $this->assertEquals($toilBalanceChange->expiry_date, $expectedExpiryDate->format('Y-m-d'));
  }

  public function testCreateForTOILWhenATOILExpiryDateIsGiven() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'max_leave_accrual' => 10,
      'allow_accruals_request' => true,
      'accrual_expiration_duration' => 10,
      'accrual_expiration_unit' => AbsenceType::EXPIRATION_UNIT_DAYS,
    ]);

    $expiryDate = new DateTime('+100 days');

    $params = $this->getDefaultTOILParams(['type_id' => $absenceType->id]);
    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation($params);

    $this->leaveBalanceChangeService->createForTOILRequest($toilRequest, $params['type_id'], $params['toil_to_accrue'], $expiryDate);
    $toilBalanceChange = $this->findToilRequestBalanceChange($toilRequest->id);

    // The settings on the AbsenceType says TOIL Requests should expire in 10 days,
    // but the expiry date passed to create was 100 days, so that should be the
    // date used
    $this->assertEquals($expiryDate->format('Y-m-d'), $toilBalanceChange->expiry_date);
  }

  private function getDefaultTOILParams($params = []) {
    $defaultParams = [
      'type_id' => 1,
      'contact_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-04-10'),
      'to_date' => CRM_Utils_Date::processDate('2016-04-12'),
      'toil_to_accrue' => 2,
      'duration' => 120,
      'expiry_date' => CRM_Utils_Date::processDate('2016-12-10')
    ];
    return array_merge($defaultParams, $params);
  }
}
