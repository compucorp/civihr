<?php

use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequest as LeaveRequestService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsencePeriod as AbsencePeriodFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeavePeriodEntitlement as LeavePeriodEntitlementFabricator;


/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestStatusMatrixHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;


  private $leaveBalanceChangeService;

  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
    $this->leaveBalanceChangeService = new LeaveBalanceChangeService();

    $this->leaveContact = 1;
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $this->leaveRequestStatuses = $this->getLeaveRequestStatuses();
  }

  public function testCreateAlsoCreateTheLeaveRequestBalanceChanges() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    // a 7 days leave request, from monday to sunday
    $leaveRequest = $this->getleaveRequestService()->create([
      'type_id' => 1,
      'contact_id' => $this->leaveContact,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-04'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-10'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
    ], false);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used, and it this is a week long
    // leave request, the balance will be -5 (for the 5 working days)
    $this->assertEquals(-5, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is 5, we must have 7 balance changes, one for
    // each date
    $this->assertCount(7, $balanceChanges);
  }

  public function testCreateDoesNotDuplicateLeaveBalanceChangesOnUpdate() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    // a 7 days leave request, from friday to thursday
    $params = [
      'type_id' => 1,
      'contact_id' => $this->leaveContact,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
    ];

    $leaveRequest = $this->getleaveRequestService()->create($params, false);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used, and it this is a week long
    // leave request, the balance will be 5 (for the 5 working days)
    $this->assertEquals(-5, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is 5, we must have 7 balance changes, one for
    // each date
    $this->assertCount(7, $balanceChanges);

    // Increase the Leave Request period by 4 days (2 weekend + 2 working days)
    $params['id'] = $leaveRequest->id;
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-11');
    $this->getleaveRequestService()->create($params, false);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // 5 from before + 2 (from the 2 new working days)
    $this->assertEquals(-7, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // 7 from before + 4 from the new period
    $this->assertCount(11, $balanceChanges);
  }

  public function testDeleteDeletesTheLeaveRequestItsBalanceChangesAndDates() {
    $leaveRequestDateTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id'        => 1,
      'contact_id'     => 1,
      'status_id'      => 1,
      'from_date'      => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $leaveRequestDateTypes['all_day'],
      'to_date'        => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type'   => $leaveRequestDateTypes['all_day'],
    ], TRUE);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $dates          = $leaveRequest->getDates();
    $this->assertCount(7, $balanceChanges);
    $this->assertCount(7, $dates);

    $this->getleaveRequestService()->delete($leaveRequest->id);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $dates          = $leaveRequest->getDates();
    $this->assertCount(0, $balanceChanges);
    $this->assertCount(0, $dates);

    try {
      $leaveRequest = LeaveRequest::findById($leaveRequest->id);
    } catch (Exception $e) {
      return;
    }

    $this->fail("Expected to not find the LeaveRequest with {$leaveRequest->id}, but it was found");
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to create or update a leave request for this employee
   */
  public function testCreateThrowsAnExceptionWhenCurrentUserDoesNotHaveCreateAndUpdateLeaveRequestPermission() {
    //logged in user has no permissions, also a contactID different from that of the logged in user is passed
    $contactID = 2;
    $params  = $this->getDefaultParams(['contact_id' => $contactID]);
    $this->getleaveRequestService()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You can't create a Leave Request with this status
   */
  public function testCreateThrowsAnExceptionWhenTransitionStatusIsNotValidForNewLeaveRequest() {
    $this->getLeaveRequestServiceWhenStatusTransitionIsNotAllowed()->create($this->getDefaultParams(), false);
  }

  public function testCreateThrowsAnExceptionWhenTransitionStatusIsNotValidWhenUpdatingLeaveRequestStatus() {
    $params = $this->getDefaultParams();
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $this->setExpectedException(
      'RuntimeException', "You can't change the Leave Request status from ".
      $this->getDefaultParams()['status_id']. " to {$this->leaveRequestStatuses['Waiting Approval']['id']}"
    );

    $params['id'] = $leaveRequest->id;
    $params['status_id'] = $this->leaveRequestStatuses['Waiting Approval']['id'];

    $this->getLeaveRequestServiceWhenStatusTransitionIsNotAllowed()->create($params, false);
  }

  public function testCreateThrowsAnExceptionForLeaveContactWhenUpdatingLeaveStatusWithoutPermission() {
    //The leave manager creates a leave request with More information requested status
    $params = $this->getDefaultParams(['status_id' => $this->leaveRequestStatuses['More Information Requested']['id']]);
    $leaveRequest = $this->getLeaveRequestServiceWhenCurrentUserIsLeaveManager()->create($params, false);

    //The leave contact tries to change the status to 'Waiting Approval'
    //Even though it was a valid status transition but the leave contact does not have the permission
    $params['id'] = $leaveRequest->id;
    $params['status_id'] = $this->leaveRequestStatuses['Waiting Approval']['id'];

    $this->setExpectedException(
      'RuntimeException', "You don't have enough permission to change the status to {$this->leaveRequestStatuses['Waiting Approval']['id']}"
    );
    $this->getLeaveRequestService()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to change the request dates
   */
  public function testCreateThrowsAnExceptionWhenLeaveApproverUpdatesDatesForLeaveRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-10');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-15');
    $params['id'] = $leaveRequest->id;

    $this->getLeaveRequestServiceWhenCurrentUserIsLeaveManager()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to change the request dates
   */
  public function testCreateThrowsAnExceptionWhenAdminUpdatesDatesForLeaveRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-10');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-15');
    $params['id'] = $leaveRequest->id;

    $this->getLeaveRequestServiceWhenCurrentUserIsAdmin()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to change the type of a request
   */
  public function testCreateThrowsAnExceptionWhenLeaveApproverUpdatesAbsenceTypeForLeaveRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $params['id'] = $leaveRequest->id;
    $params['type_id'] = 2;

    $this->getLeaveRequestServiceWhenCurrentUserIsLeaveManager()->create($params, false);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to change the type of a request
   */
  public function testCreateThrowsAnExceptionWhenAdminUpdatesAbsenceTypeForLeaveRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    $params['id'] = $leaveRequest->id;
    $params['type_id'] = 2;

    $this->getLeaveRequestServiceWhenCurrentUserIsAdmin()->create($params, false);
  }

  private function getLeaveRequestService($isAdmin = false, $isManager = false, $allowStatusTransition = true) {
    $leaveManagerService = $this->createLeaveManagerServiceMock($isAdmin, $isManager);
    $leaveRequestStatusMatrixService = $this->createLeaveRequestStatusMatrixServiceMock($allowStatusTransition);
    $leaveRequestRightsService = new LeaveRequestRightsService($leaveManagerService);

    return new LeaveRequestService(
      $this->leaveBalanceChangeService,
      $leaveRequestStatusMatrixService,
      $leaveRequestRightsService
    );
  }

  public function testExpiredBalanceChangeIsRecalculatedOnCreateWhenLeaveRequestHasApprovedPastDatesAndThereAreExpiredBalanceChangesWithinTheLeaveRequestDatesInterval() {
    $absencePeriod = AbsencePeriodFabricator::fabricate([
      'start_date' => CRM_Utils_Date::processDate('-10 days'),
      'end_date' => CRM_Utils_Date::processDate('+5 days')
    ]);

    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => CRM_Utils_Date::processDate('-10 days')]
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $params = $this->getDefaultParams([
      'from_date' => CRM_Utils_Date::processDate('-2 days'),
      'to_date' => CRM_Utils_Date::processDate('+1 day'),
      'status' => 1
    ]);

    $periodEntitlement1 = LeavePeriodEntitlementFabricator::fabricate([
      'contact_id' => $params['contact_id'],
      'period_id' => $absencePeriod->id,
      'type_id' => $params['type_id'],
    ]);

    $balanceChange = $this->createExpiredBroughtForwardBalanceChange(
      $periodEntitlement1->id,
      5,
      5,
      2
    );

    // A leave request with past dates with the first day on the day
    // the brought forward balance change expired
    $this->getLeaveRequestServiceWhenCurrentUserIsAdmin()->create($params, false);

    $expiryRecord = new LeaveBalanceChange();
    $expiryRecord->id = $balanceChange->id;
    $expiryRecord->find(true);

    //Balance change is recalculated to 4 days
    $this->assertEquals(-4, $expiryRecord->amount);
  }

  private function getLeaveRequestServiceWhenStatusTransitionIsNotAllowed() {
    return $this->getLeaveRequestService(false, false, false);
  }

  private function getLeaveRequestServiceWhenCurrentUserIsAdmin() {
    return $this->getLeaveRequestService(true, false);
  }

  private function getLeaveRequestServiceWhenCurrentUserIsLeaveManager() {
    return $this->getLeaveRequestService(false, true);
  }

  private function getDefaultParams($params = []) {
    $defaultParams =  [
      'type_id' => 1,
      'contact_id' => $this->leaveContact,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-04'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-10'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
    ];
    return array_merge($defaultParams, $params);
  }
}
