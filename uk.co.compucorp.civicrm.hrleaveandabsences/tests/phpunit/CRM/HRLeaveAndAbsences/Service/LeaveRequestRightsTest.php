<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestRightsTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestRightsTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  /**
   * @var int
   */
  private $leaveContact;

  public function setUp() {
    $this->leaveContact = 1;
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $this->leaveRequestStatuses = $this->getLeaveRequestStatuses();
  }

  public function testCanCancelForReturnsTrueWhenCurrentUserIsLeaveRequestContact() {
    $this->assertTrue($this->getLeaveRightsService()->canCancelFor($this->leaveContact));
  }

  public function testCanCancelForReturnsTrueWhenCurrentUserIsLeaveManager() {
    $this->assertTrue($this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canCancelFor($this->leaveContact));
  }

  public function testCanCancelForReturnsTrueWhenCurrentUserIsAdmin() {
    $this->assertTrue($this->getLeaveRequestRightsForAdminAsCurrentUser()->canCancelFor($this->leaveContact));
  }

  public function testCanCancelForReturnsFalseWhenCurrentUserIsNotAnAdminOrLeaveManagerOrLeaveContact() {
    $contactID = 3;
    $this->assertFalse($this->getLeaveRightsService()->canCancelFor($contactID));
  }

  public function testCanApproveForReturnsTrueWhenCurrentUserIsLeaveManager() {
    $this->assertTrue($this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canApproveFor($this->leaveContact));
  }

  public function testCanApproveForReturnsTrueWhenCurrentUserIsAdmin() {
    $this->assertTrue($this->getLeaveRequestRightsForAdminAsCurrentUser()->canApproveFor($this->leaveContact));
  }

  public function testCanApproveForReturnsFalseWhenCurrentUserIsLeaveContact() {
    $this->assertFalse($this->getLeaveRightsService()->canApproveFor($this->leaveContact));
  }

  public function testCanRejectForReturnsTrueWhenCurrentUserIsLeaveManager() {
    $this->assertTrue($this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canRejectFor($this->leaveContact));
  }

  public function testCanRejectForReturnsTrueWhenCurrentUserIsAdmin() {
    $this->assertTrue($this->getLeaveRequestRightsForAdminAsCurrentUser()->canRejectFor($this->leaveContact));
  }

  public function testCanRejectForReturnsFalseWhenCurrentUserIsLeaveContact() {
    $this->assertFalse($this->getLeaveRightsService()->canRejectFor($this->leaveContact));
  }

  public function testCanRequestMoreInformationForReturnsTrueWhenCurrentUserIsLeaveManager() {
    $this->assertTrue($this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canRequestMoreInformationFor($this->leaveContact));
  }

  public function testCanRequestMoreInformationForReturnsTrueWhenCurrentUserIsAdmin() {
    $this->assertTrue($this->getLeaveRequestRightsForAdminAsCurrentUser()->canRequestMoreInformationFor($this->leaveContact));
  }

  public function testCanRequestMoreInformationForReturnsFalseWhenCurrentUserIsLeaveContact() {
    $this->assertFalse($this->getLeaveRightsService()->canRequestMoreInformationFor($this->leaveContact));
  }

  public function testCanPutInWaitingForApprovalForReturnsTrueWhenCurrentUserIsLeaveManager() {
    $this->assertTrue($this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canPutInWaitingForApprovalFor($this->leaveContact));
  }

  public function testCanPutInWaitingForApprovalForReturnsTrueWhenCurrentUserIsAdmin() {
    $this->assertTrue($this->getLeaveRequestRightsForAdminAsCurrentUser()->canPutInWaitingForApprovalFor($this->leaveContact));
  }

  public function testCanPutInWaitingForApprovalForReturnsFalseWhenCurrentUserIsLeaveContact() {
    $this->assertFalse($this->getLeaveRightsService()->canPutInWaitingForApprovalFor($this->leaveContact));
  }

  public function testCanCreateAndUpdateForReturnsTrueWhenCurrentUserIsLeaveRequestContact() {
    $this->assertTrue($this->getLeaveRightsService()->canCreateAndUpdateFor($this->leaveContact));
  }

  public function testCanCreateAndUpdateForReturnsTrueWhenCurrentUserIsLeaveManager() {
    $this->assertTrue($this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canCreateAndUpdateFor($this->leaveContact));
  }

  public function testCanCreateAndUpdateForReturnsTrueWhenCurrentUserIsAdmin() {
    $this->assertTrue($this->getLeaveRequestRightsForAdminAsCurrentUser()->canCreateAndUpdateFor($this->leaveContact));
  }

  public function testCanCreateAndUpdateForReturnsFalseWhenCurrentUserIsNotAnAdminOrLeaveManagerOrLeaveContact() {
    $contactID = 3;
    $this->assertFalse($this->getLeaveRightsService()->canCreateAndUpdateFor($contactID));
  }

  /**
   * @dataProvider openLeaveRequestStatusesDataProvider
   */
  public function testCanChangeDatesForReturnsTrueForAllRequestTypesWhenCurrentUserIsLeaveContactAndTheLeaveRequestIsOpen($status) {
    $this->assertTrue(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $status,
        LeaveRequest::REQUEST_TYPE_LEAVE
      )
    );

    $this->assertTrue(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $status,
        LeaveRequest::REQUEST_TYPE_TOIL
      )
    );

    $this->assertTrue(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $status,
        LeaveRequest::REQUEST_TYPE_SICKNESS
      )
    );
  }

  /**
   * @dataProvider closedLeaveRequestStatusesDataProvider
   */
  public function testCanChangeDatesForReturnsFalseForAllRequestTypesWhenCurrentUserIsLeaveContactAndTheLeaveRequestIsClosed($status) {
    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $status,
        LeaveRequest::REQUEST_TYPE_LEAVE
      )
    );

    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $status,
        LeaveRequest::REQUEST_TYPE_TOIL
      )
    );

    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $status,
        LeaveRequest::REQUEST_TYPE_SICKNESS
      )
    );
  }

  /**
   * @dataProvider leaveRequestStatusesDataProvider
   */
  public function testCanChangeDatesForReturnsFalseForTOILAndLeaveRequestTypesWhenCurrentUserNotLeaveContactIrrespectiveOfStatusPassed($status) {
    $contactID = 2;
    $managerRightsService = $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser();
    $adminRightsService = $this->getLeaveRequestRightsForAdminAsCurrentUser();
    $staffRightsService = $this->getLeaveRightsService();

    $this->assertFalse(
      $managerRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_LEAVE)
    );

    $this->assertFalse(
      $adminRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_LEAVE)
    );

    $this->assertFalse(
      $staffRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_LEAVE)
    );

    $this->assertFalse(
      $managerRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_TOIL)
    );

    $this->assertFalse(
      $adminRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_TOIL)
    );

    $this->assertFalse(
      $staffRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_TOIL)
    );
  }

  /**
   * @dataProvider openLeaveRequestStatusesDataProvider
   */
  public function testCanChangeDatesForReturnsTrueForSicknessRequestTypeWhenCurrentUserIsLeaveManagerOrAdminAndTheLeaveRequestIsOpen($status) {
    $contactID = 2;
    $managerRightsService = $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser();
    $adminRightsService = $this->getLeaveRequestRightsForAdminAsCurrentUser();

    $this->assertTrue(
      $managerRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_SICKNESS)
    );

    $this->assertTrue(
      $adminRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_SICKNESS)
    );
  }

  /**
   * @dataProvider closedLeaveRequestStatusesDataProvider
   */
  public function testCanChangeDatesForReturnsFalseForSicknessRequestTypeWhenCurrentUserIsLeaveManagerOrAdminAndTheLeaveRequestIsClosed($status) {
    $contactID = 2;
    $managerRightsService = $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser();
    $adminRightsService = $this->getLeaveRequestRightsForAdminAsCurrentUser();

    $this->assertFalse(
      $managerRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_SICKNESS)
    );

    $this->assertFalse(
      $adminRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_SICKNESS)
    );
  }

  public function testCanChangeAbsenceTypeForReturnsTrueWhenCurrentUserIsLeaveContactAndStatusPassedIsInAllowedStatuses() {
    //When user is leave request contact and status is 'More information Requested'
    $this->assertTrue(
      $this->getLeaveRightsService()->canChangeAbsenceTypeFor(
        $this->leaveContact,
        $this->leaveRequestStatuses['More Information Requested']['id']
      )
    );

    //When user is leave request contact and status is 'Waiting Approval'
    $this->assertTrue(
      $this->getLeaveRightsService()->canChangeAbsenceTypeFor(
        $this->leaveContact,
        $this->leaveRequestStatuses['Waiting Approval']['id']
      )
    );
  }

  public function testCanChangeAbsenceTypeForReturnsFalseWhenCurrentUserIsLeaveContactAndStatusPassedIsNotInAllowedStatuses() {
    //When user is leave request contact and status is 'Approved'
    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeAbsenceTypeFor(
        $this->leaveContact,
        $this->leaveRequestStatuses['Approved']['id']
      )
    );

    //When user is leave request contact and status is 'Admin Approved'
    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeAbsenceTypeFor(
        $this->leaveContact,
        $this->leaveRequestStatuses['Admin Approved']['id']
      )
    );
  }

  /**
   * @dataProvider leaveRequestStatusesDataProvider
   */
  public function testCanChangeAbsenceTypeForReturnsFalseWhenCurrentUserNotLeaveContactIrrespectiveOfStatusPassed($status) {
    $contactID = 2;

    $this->assertFalse(
      $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canChangeAbsenceTypeFor(
        $contactID,
        $status
      )
    );

    $this->assertFalse(
      $this->getLeaveRequestRightsForAdminAsCurrentUser()->canChangeAbsenceTypeFor(
        $contactID,
        $status
      )
    );

    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeAbsenceTypeFor(
        $contactID,
        $status
      )
    );
  }

  private function getLeaveRightsService($isAdmin = false, $isManager = false) {
    $leaveManagerService = $this->createLeaveManagerServiceMock($isAdmin, $isManager);
    return new LeaveRequestRightsService($leaveManagerService);
  }

  private function getLeaveRequestRightsForAdminAsCurrentUser() {
    return $this->getLeaveRightsService(true, false);
  }

  private function getLeaveRequestRightsForLeaveManagerAsCurrentUser() {
    return $this->getLeaveRightsService(false, true);
  }

  public function leaveRequestStatusesDataProvider() {
    $leaveRequestStatuses =  $this->getLeaveRequestStatuses();

    return [
      [$leaveRequestStatuses['More Information Requested']['id']],
      [$leaveRequestStatuses['Waiting Approval']['id']],
      [$leaveRequestStatuses['Cancelled']['id']],
      [$leaveRequestStatuses['Rejected']['id']],
      [$leaveRequestStatuses['Admin Approved']['id']],
      [$leaveRequestStatuses['Approved']['id']],
    ];
  }
}
