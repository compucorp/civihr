<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveManagerTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;

  private $leaveManagerService;

  private $loggedInContact;

  public function setUp() {
    $this->loggedInContact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession();
    $this->leaveManagerService = new LeaveManagerService();
  }

  public function tearDown() {
    $this->unregisterCurrentLoggedInContactFromSession();
  }

  public function testIsContactManagedBy() {
    $manager = ContactFabricator::fabricate();
    $staffMember = ContactFabricator::fabricate();

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));

    $this->setContactAsLeaveApproverOf($manager, $staffMember);

    $this->assertTrue($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));
  }

  public function testIsContactManagedByWhenTheRelationshipHasSpecificStartDate() {
    $manager = ContactFabricator::fabricate();
    $staffMember = ContactFabricator::fabricate();

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));

    $today = new DateTime('today');
    $this->setContactAsLeaveApproverOf($manager, $staffMember, $today->format('Y-m-d'));

    $this->assertTrue($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));
  }

  public function testIsContactManagedByWhenTheRelationshipHasSpecificDates() {
    $manager = ContactFabricator::fabricate();
    $staffMember = ContactFabricator::fabricate();

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));

    $today = new DateTime('today');
    $tomorrow = new DateTime('tomorrow');
    $this->setContactAsLeaveApproverOf($manager, $staffMember, $today->format('Y-m-d'), $tomorrow->format('Y-m-d'));

    $this->assertTrue($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));
  }

  public function testIsContactManagedByWhenTheresNoActiveRelationshipForTheCurrentDate() {
    $manager = ContactFabricator::fabricate();
    $staffMember = ContactFabricator::fabricate();

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));

    // Set a relationship in the past
    $startDate = new DateTime('-10 days');
    $endDate = new DateTime('-1 day');
    $this->setContactAsLeaveApproverOf($manager, $staffMember, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));

    // Set a relationship in the future
    $startDate = new DateTime('+1 day');
    $this->setContactAsLeaveApproverOf($manager, $staffMember, $startDate->format('Y-m-d'));

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));
  }

  public function testIsContactManagedByWhenTheCurrentRelationshipIsNotActive() {
    $manager = ContactFabricator::fabricate();
    $staffMember = ContactFabricator::fabricate();

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));

    $this->setContactAsLeaveApproverOf($manager, $staffMember, null, null, false);

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($staffMember['id'], $manager['id']));
  }

  public function testCurrentUserIsLeaveManagerOf() {
    $staffMember = ContactFabricator::fabricate();

    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($staffMember['id']));

    $this->setContactAsLeaveApproverOf($this->loggedInContact, $staffMember);

    $this->assertTrue($this->leaveManagerService->currentUserIsLeaveManagerOf($staffMember['id']));
  }

  public function testCurrentUserIsLeaveManagerOfWhenTheresNoActiveRelationshipForTheCurrentDate() {
    $staffMember = ContactFabricator::fabricate();

    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($staffMember['id']));

    // Set a relationship in the past
    $startDate = new DateTime('-10 days');
    $endDate = new DateTime('-1 day');
    $this->setContactAsLeaveApproverOf($this->loggedInContact, $staffMember, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($staffMember['id']));

    // Set a relationship in the future
    $startDate = new DateTime('+1 day');
    $this->setContactAsLeaveApproverOf($this->loggedInContact, $staffMember, $startDate->format('Y-m-d'));

    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($staffMember['id']));
  }

  public function testCurrentUserIsLeaveManagerOfWhenTheCurrentRelationshipIsNotActive() {
    $staffMember = ContactFabricator::fabricate();

    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($staffMember['id']));
    
    $this->setContactAsLeaveApproverOf($this->loggedInContact, $staffMember, null, null, false);

    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($staffMember['id']));
  }

  public function testCurrentUserIsAdmin() {
    // First we need to make sure no permission is set. Note that if
    // permissions is null, CRM_Core_Permission always returns true, so we must
    // manually set to an empty array
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $this->assertFalse($this->leaveManagerService->currentUserIsAdmin());
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $this->assertTrue($this->leaveManagerService->currentUserIsAdmin());
  }

  private function registerCurrentLoggedInContactInSession() {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', $this->loggedInContact['id']);
  }

  private function unregisterCurrentLoggedInContactFromSession() {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', null);
  }
}
