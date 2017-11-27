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
  }

  public function testCanCreateAndUpdateForReturnsFalseWhenCurrentUserIsNotAnAdminOrLeaveManagerOrLeaveContact() {
    $contactID = 3;
    $this->assertFalse($this->getLeaveRightsService()->canCreateAndUpdateFor($contactID));
  }

  public function testCanDeleteForReturnsFalseWhenCurrentUserIsLeaveContact() {
    $this->assertFalse($this->getLeaveRightsService()->canDeleteFor($this->leaveContact));
  }

  public function testCanDeleteForReturnsFalseWhenCurrentUserIsLeaveManager() {
    $this->assertFalse($this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canDeleteFor($this->leaveContact));
  }

  public function testCanDeleteForReturnsTrueWhenCurrentUserIsAdmin() {
    $this->assertTrue($this->getLeaveRequestRightsForAdminAsCurrentUser()->canDeleteFor($this->leaveContact));
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
  public function testCanChangeDatesForReturnsFalseForAnyRequestTypeWhenCurrentUserIsStaffAndNotLeaveContactIrrespectiveOfStatusPassed($status) {
    $contactID = 2;
    $staffRightsService = $this->getLeaveRightsService();

    $this->assertFalse(
      $staffRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_LEAVE)
    );

    $this->assertFalse(
      $staffRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_SICKNESS)
    );

    $this->assertFalse(
      $staffRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_TOIL)
    );
  }

  /**
   * @dataProvider leaveRequestStatusesDataProvider
   */
  public function testCanChangeDatesForReturnsFalseForTOILAndLeaveRequestTypesWhenCurrentUserIsLeaveManagerIrrespectiveOfStatusPassed($status) {
    $contactID = 2;
    $managerRightsService = $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser();

    $this->assertFalse(
      $managerRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_LEAVE)
    );

    $this->assertFalse(
      $managerRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_TOIL)
    );
  }

  /**
   * @dataProvider leaveRequestStatusesDataProvider
   */
  public function testCanChangeDatesForReturnsTrueForSicknessRequestTypeWhenCurrentUserIsLeaveManagerIrrespectiveOfStatusPassed($status) {
    $contactID = 2;
    $managerRightsService = $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser();

    $this->assertTrue(
      $managerRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_SICKNESS)
    );
  }

  /**
   * @dataProvider leaveRequestStatusesDataProvider
   */
  public function testCanChangeDatesForReturnsTrueForAnyRequestTypeWhenCurrentUserIsAdminIrrespectiveOfStatusPassed($status) {
    $contactID = 2;
    $adminRightsService = $this->getLeaveRequestRightsForAdminAsCurrentUser();

    $this->assertTrue(
      $adminRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_LEAVE)
    );

    $this->assertTrue(
      $adminRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_SICKNESS)
    );

    $this->assertTrue(
      $adminRightsService->canChangeDatesFor($contactID, $status, LeaveRequest::REQUEST_TYPE_TOIL)
    );
  }

  /**
   * @dataProvider openLeaveRequestStatusesDataProvider
   */
  public function testCanChangeAbsenceTypeForReturnsTrueWhenCurrentUserIsLeaveContactAndTheLeaveRequestIsOpen($status) {
    $this->assertTrue(
      $this->getLeaveRightsService()->canChangeAbsenceTypeFor(
        $this->leaveContact,
        $status
      )
    );
  }

  /**
   * @dataProvider approvedLeaveRequestStatusesDataProvider
   */
  public function testCanChangeAbsenceTypeForReturnsFalseWhenCurrentUserIsLeaveContactAndTheLeaveRequestIsApproved($status) {
    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeAbsenceTypeFor(
        $this->leaveContact,
        $status
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
      [$leaveRequestStatuses['more_information_required']],
      [$leaveRequestStatuses['awaiting_approval']],
      [$leaveRequestStatuses['cancelled']],
      [$leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['admin_approved']],
      [$leaveRequestStatuses['approved']],
    ];
  }
}
