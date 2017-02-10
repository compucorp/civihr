<?php

use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_SicknessRequest as SicknessRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern as WorkPatternFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_SicknessRequest as SicknessRequestFabricator;
use CRM_HRLeaveAndAbsences_Service_SicknessRequest as SicknessRequestService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix as LeaveRequestStatusMatrixService;

/**
 * Class CRM_HRLeaveAndAbsences_Service_SicknessRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_SicknessRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $leaveBalanceChangeService;

  private $leaveContact;

  private $sicknessReasons;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    $this->sicknessReasons = array_flip(SicknessRequest::buildOptions('reason', 'validate'));
    $this->leaveBalanceChangeService = new LeaveBalanceChangeService();
    $this->leaveContact = 1;
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];
  }

  public function testCreateAlsoCreateTheLeaveRequestBalanceChanges() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $service = $this->getSicknessRequestService();

    // a 9 days request, from thursday to friday next week
    $sicknessRequest = $service->create([
      'type_id' => 1,
      'contact_id' => $this->leaveContact,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-02-11'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-02-19'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'reason' => $this->sicknessReasons['accident']
    ], false);

    $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used and there are 7 working days during
    // the 9 days period of the requests, the balance will be -7
    $this->assertEquals(-7, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is -7, we must have 9 balance changes, one for
    // each date
    $this->assertCount(9, $balanceChanges);
  }

  public function testCreateDoesNotDuplicateLeaveBalanceChangesOnUpdate() {
    HRJobContractFabricator::fabricate(
      ['contact_id' => $this->leaveContact],
      ['period_start_date' => '2016-01-01']
    );

    WorkPatternFabricator::fabricateWithA40HourWorkWeek(['is_default']);

    $service = $this->getSicknessRequestService();

    $params = [
      'type_id' => 1,
      'contact_id' => $this->leaveContact,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'reason' => $this->sicknessReasons['accident']
    ];

    // a 7 days leave request, from friday to thursday
    $sicknessRequest = $service->create($params, false);

    $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // Since the 40 hours work pattern was used, and it this is a week long
    // leave request, the balance will be -5 (for the 5 working days)
    $this->assertEquals(-5, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // Even though the balance is -5, we must have 7 balance changes, one for
    // each date
    $this->assertCount(7, $balanceChanges);

    // Increase the Leave Request period by 4 days (2 weekend + 2 working days)
    $params['id'] = $sicknessRequest->id;
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-11');
    $sicknessRequest = $service->create($params, false);

    // the associated leave request is still the same
    $this->assertEquals($leaveRequest->id, $sicknessRequest->leave_request_id);

    $balance = LeaveBalanceChange::getTotalBalanceChangeForLeaveRequest($leaveRequest);
    // -5 from before - 2 (from the 2 new working days)
    $this->assertEquals(-7, $balance);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    // 7 from before + 4 from the new period
    $this->assertCount(11, $balanceChanges);
  }

  public function testDeleteDeletesTheLeaveRequestItsBalanceChangesAndDates() {
    $sicknessRequest = SicknessRequestFabricator::fabricateWithoutValidation([
      'type_id'        => 1,
      'contact_id'     => 1,
      'status_id'      => 1,
      'from_date'      => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date'        => CRM_Utils_Date::processDate('2016-01-07'),
      'to_date_type'   => $this->getLeaveRequestDayTypes()['All Day']['value'],
    ], TRUE);

    $leaveRequest = LeaveRequest::findById($sicknessRequest->leave_request_id);

    $balanceChanges = LeaveBalanceChange::getBreakdownForLeaveRequest($leaveRequest);
    $dates          = $leaveRequest->getDates();
    $this->assertCount(7, $balanceChanges);
    $this->assertCount(7, $dates);

    $service = $this->getSicknessRequestService();
    $service->delete($sicknessRequest->id);

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

  public function testCreateDoesNotThrowAnExceptionWhenLeaveManagerUpdatesDatesForSicknessRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $sicknessRequest = SicknessRequestFabricator::fabricateWithoutValidation($params);

    $service = $this->getSicknessRequestServiceWhenCurrentUserIsLeaveManager();
    $params['id'] = $sicknessRequest->id;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-10');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-15');
    $sicknessRequest  = $service->create($params, false);
    $this->assertInstanceOf(CRM_HRLeaveAndAbsences_BAO_SicknessRequest::class, $sicknessRequest);
  }

  public function testCreateDoesNotThrowAnExceptionWhenAdminUpdatesDatesForSicknessRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $sicknessRequest = SicknessRequestFabricator::fabricateWithoutValidation($params);

    $service = $this->getSicknessRequestServiceWhenCurrentUserIsAdmin();
    $params['id'] = $sicknessRequest->id;
    $params['from_date'] = CRM_Utils_Date::processDate('2016-01-10');
    $params['to_date'] = CRM_Utils_Date::processDate('2016-01-15');
    $sicknessRequest  = $service->create($params, false);
    $this->assertInstanceOf(CRM_HRLeaveAndAbsences_BAO_SicknessRequest::class, $sicknessRequest);
  }

  /**
   * @expectedException RuntimeException
   * @expectedExceptionMessage You are not allowed to change the type of a request
   */
  public function testCreateThrowsAnExceptionWhenLeaveApproverUpdatesAbsenceTypeForSicknessRequest() {
    $contactID = 5;
    $params = $this->getDefaultParams(['contact_id' => $contactID]);
    $sicknessRequest = SicknessRequestFabricator::fabricateWithoutValidation($params);

    $service = $this->getSicknessRequestServiceWhenCurrentUserIsLeaveManager();
    $params['id'] = $sicknessRequest->id;
    $params['type_id'] = 2;
    $sicknessRequest  = $service->create($params, false);
    $this->assertInstanceOf(CRM_HRLeaveAndAbsences_BAO_SicknessRequest::class, $sicknessRequest);
  }

  private function getSicknessRequestService($isAdmin = false, $isManager = false) {
    $leaveManagerService = $this->createLeaveManagerServiceMock($isAdmin, $isManager);
    $leaveRequestStatusMatrixService = new LeaveRequestStatusMatrixService($leaveManagerService);
    $leaveRequestRightsService = new LeaveRequestRightsService($leaveManagerService);

    return new SicknessRequestService(
      $this->leaveBalanceChangeService,
      $leaveRequestStatusMatrixService,
      $leaveRequestRightsService
    );
  }

  private function getSicknessRequestServiceWhenCurrentUserIsAdmin() {
    return $this->getSicknessRequestService(true, false);
  }

  private function getSicknessRequestServiceWhenCurrentUserIsLeaveManager() {
    return $this->getSicknessRequestService(false, true);
  }

  private function getDefaultParams($params = []) {
    $defaultParams = [
      'type_id' => 1,
      'contact_id' => $this->leaveContact,
      'status_id' => 3,
      'from_date' => CRM_Utils_Date::processDate('2016-01-11'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('2016-01-09'),
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'reason' => $this->sicknessReasons['accident']
    ];

    return array_merge($defaultParams, $params);
  }
}
