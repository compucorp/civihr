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

  /**
   * @var int
   */
  private $leaveContact;

  public function setUp() {
    $this->leaveContact = 1;
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];
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
    $this->assertTrue($this->getLeaveRequestRightsForAdminAsCurrentUser()->canPutInWaitingForApprovalFor($this->leaveContact['id']));
  }

  public function testCanPutInWaitingForApprovalForReturnsFalseWhenCurrentUserIsLeaveContact() {
    $this->assertFalse($this->getLeaveRightsService()->canPutInWaitingForApprovalFor($this->leaveContact));
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
