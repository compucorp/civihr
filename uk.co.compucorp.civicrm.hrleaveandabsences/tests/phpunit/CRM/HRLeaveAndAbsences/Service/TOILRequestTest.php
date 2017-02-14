<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_TOILRequest as TOILRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_TOILRequest as TOILRequestFabricator;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix as LeaveRequestStatusMatrixService;
use CRM_HRLeaveAndAbsences_Service_LeaveBalanceChange as LeaveBalanceChangeService;
use CRM_HRLeaveAndAbsences_Service_TOILRequest as TOILRequestService;

/**
 * Class CRM_HRLeaveAndAbsences_Service_TOILRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_TOILRequestTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveBalanceChangeHelpersTrait;
  use CRM_HRLeaveAndAbsences_TOILRequestHelpersTrait;

  private $leaveContact;

  private $absenceType;

  private $leaveBalanceChangeService;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');

    $this->absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => true,
      'max_leave_accrual' => 10
    ]);

    $this->leaveBalanceChangeService = new LeaveBalanceChangeService();

    $this->leaveContact = 1;
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];
  }

  public function testCreateAlsoCreatesTheLeaveRequestAndTheTOILRequestBalanceChange() {
    $toilRequestService = $this->getTOILRequestService();
    $params = $this->getDefaultParams();
    $toilRequest = $toilRequestService->create($params, false);

    LeaveRequest::findById($toilRequest->leave_request_id);
    $toilBalanceChange = $this->findToilRequestBalanceChange($toilRequest->id);
    $this->assertEquals($toilBalanceChange->amount, $params['toil_to_accrue']);
  }

  public function testDeleteAlsoDeletesTheLeaveRequestItsBalanceChangesAndDates() {
    $params = $this->getDefaultParams([
      'from_date' => CRM_Utils_Date::processDate('+1 day'),
      'to_date' => CRM_Utils_Date::processDate('+3 days'),
    ]);
    $toilRequest = TOILRequestFabricator::fabricateWithoutValidation($params, true);

    $leaveRequest = LeaveRequest::findById($toilRequest->leave_request_id);
    $toilBalanceChange = $this->findToilRequestBalanceChange($toilRequest->id);
    $leaveRequestDates = $leaveRequest->getDates();
    $this->assertCount(3, $leaveRequestDates);
    $this->assertInstanceOf(LeaveBalanceChange::class, $toilBalanceChange);

    //delete the TOIL
    $service = $this->getTOILRequestService();
    $service->delete($toilRequest->id);

    $this->assertNull($this->findToilRequestBalanceChange($toilRequest->id));
    $dates = $leaveRequest->getDates();
    $this->assertCount(0, $dates);

    try {
      LeaveRequest::findById($leaveRequest->id);
      $this->fail("Expected to not find the LeaveRequest with {$leaveRequest->id}, but it was found");
    } catch (Exception $e) {
      return;
    }

    try {
      TOILRequest::findById($toilRequest->id);
      $this->fail("Expected to not find the TOILRequest with {$toilRequest->id}, but it was found");
    } catch (Exception $e) {
      return;
    }
  }

  private function getTOILRequestService($isAdmin = false, $isManager = false) {
    $leaveManagerService = $this->createLeaveManagerServiceMock($isAdmin, $isManager);
    $leaveRequestStatusMatrixService = new LeaveRequestStatusMatrixService($leaveManagerService);
    $leaveRequestRightsService = new LeaveRequestRightsService($leaveManagerService);

    return new TOILRequestService(
      $this->leaveBalanceChangeService,
      $leaveRequestStatusMatrixService,
      $leaveRequestRightsService
    );
  }

  private function getDefaultParams($params = []) {
    $defaultParams = [
      'contact_id' => $this->leaveContact,
      'type_id' => $this->absenceType->id,
      'status_id' => $this->getLeaveRequestStatuses()['Waiting Approval']['value'],
      'from_date' => CRM_Utils_Date::processDate('today'),
      'to_date' => CRM_Utils_Date::processDate('today'),
      'from_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'to_date_type' => $this->getLeaveRequestDayTypes()['All Day']['value'],
      'toil_to_accrue' => 2,
      'duration' => 60,
      'sequential' => 1
    ];

    return array_merge($defaultParams, $params);
  }
}
