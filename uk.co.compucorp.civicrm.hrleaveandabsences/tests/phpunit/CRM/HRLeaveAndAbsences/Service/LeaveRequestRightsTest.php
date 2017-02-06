<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;

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

  public function testCanChangeDatesForReturnsTrueWhenCurrentUserIsLeaveContactAndStatusPassedIsInAllowedStatuses() {
    //When user is leave request contact and status is 'More information Requested'
    $this->assertTrue(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $this->leaveRequestStatuses['More Information Requested']['id']
      )
    );

    //When user is leave request contact and status is 'Waiting Approval'
    $this->assertTrue(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $this->leaveRequestStatuses['Waiting Approval']['id']
      )
    );
  }

  public function testCanChangeDatesForReturnsFalseWhenCurrentUserIsLeaveContactAndStatusPassedIsNotInAllowedStatuses() {
    //When user is leave request contact and status is 'Approved'
    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $this->leaveRequestStatuses['Approved']['id']
      )
    );

    //When user is leave request contact and status is 'Admin Approved'
    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $this->leaveContact,
        $this->leaveRequestStatuses['Admin Approved']['id']
      )
    );
  }

  public function testCanChangeDatesForReturnsFalseWhenCurrentUserNotLeaveContactIrrespectiveOfStatusPassed() {
    $contactID = 2;

    $this->assertFalse(
      $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canChangeDatesFor(
        $contactID,
        $this->leaveRequestStatuses['More Information Requested']['id']
      )
    );

    $this->assertFalse(
      $this->getLeaveRequestRightsForAdminAsCurrentUser()->canChangeDatesFor(
        $contactID,
        $this->leaveRequestStatuses['Admin Approved']['id']
      )
    );

    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeDatesFor(
        $contactID,
        $this->leaveRequestStatuses['Approved']['id']
      )
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

  public function testCanChangeAbsenceTypeForReturnsFalseWhenCurrentUserNotLeaveContactIrrespectiveOfStatusPassed() {
    $contactID = 2;

    $this->assertFalse(
      $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canChangeAbsenceTypeFor(
        $contactID,
        $this->leaveRequestStatuses['More Information Requested']['id']
      )
    );

    $this->assertFalse(
      $this->getLeaveRequestRightsForAdminAsCurrentUser()->canChangeAbsenceTypeFor(
        $contactID,
        $this->leaveRequestStatuses['Admin Approved']['id']
      )
    );

    $this->assertFalse(
      $this->getLeaveRightsService()->canChangeAbsenceTypeFor(
        $contactID,
        $this->leaveRequestStatuses['Approved']['id']
      )
    );
  }

  private function getLeaveRightsService($isAdmin = false, $isManager = false) {
    $leaveManagerService = $this->createLeaveLeaveManagerServiceMock($isAdmin, $isManager);
    return new LeaveRequestRightsService($leaveManagerService);
  }

  private function getLeaveRequestRightsForAdminAsCurrentUser() {
    return $this->getLeaveRightsService(true, false);
  }

  private function getLeaveRequestRightsForLeaveManagerAsCurrentUser() {
    return $this->getLeaveRightsService(false, true);
  }
}
