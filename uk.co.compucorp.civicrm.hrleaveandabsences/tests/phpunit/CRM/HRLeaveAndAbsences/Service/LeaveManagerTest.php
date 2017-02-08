<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveManagerTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $leaveManagerService;
  private $manager;
  private $staffMember;

  public function setUp() {
    $this->manager = ContactFabricator::fabricate();
    $this->staffMember = ContactFabricator::fabricate();

    $this->registerCurrentLoggedInContactInSession($this->manager['id']);

    $this->leaveManagerService = new LeaveManagerService();
  }

  public function tearDown() {
    $this->unregisterCurrentLoggedInContactFromSession();
  }

  public function testIsContactManagedBy() {
    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));

    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember);

    $this->assertTrue($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));
  }

  public function testIsContactManagedByWhenTheRelationshipHasSpecificStartDate() {
    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));

    $today = new DateTime('today');
    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, $today->format('Y-m-d'));

    $this->assertTrue($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));
  }

  public function testIsContactManagedByWhenTheRelationshipHasSpecificDates() {
    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));

    $today = new DateTime('today');
    $tomorrow = new DateTime('tomorrow');
    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, $today->format('Y-m-d'), $tomorrow->format('Y-m-d'));

    $this->assertTrue($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));
  }

  public function testIsContactManagedByWhenTheresNoActiveRelationshipForTheCurrentDate() {
    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));

    // Set a relationship in the past
    $startDate = new DateTime('-10 days');
    $endDate = new DateTime('-1 day');
    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));

    // Set a relationship in the future
    $startDate = new DateTime('+1 day');
    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, $startDate->format('Y-m-d'));

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));
  }

  public function testIsContactManagedByWhenRelationshipIsNotActive() {
    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));

    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, null, null, false);

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));
  }

  public function testIsContactManagedByWhenThereAreMultipleLeaveApproverRelationshipsAndOnlyOneIsActive() {
    $this->setLeaveApproverRelationshipTypes([
      'approves leaves for',
      'manages leaves for'
    ]);

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));

    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, null, null, false, 'approves leaves for');

    // the relationship is of one of the "Leave Approver" types, but it's not active,
    // so this should return false
    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));

    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, null, null, true, 'manages leaves for');

    // this relationship uses another one of the "Leave Approver" types and it's active,
    // so this should return true
    $this->assertTrue($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));
  }

  public function testIsContactManagedByWhenThereAreMultipleActiveLeaveApproverRelationships() {
    $this->setLeaveApproverRelationshipTypes([
      'approves leaves for',
      'manages leaves for'
    ]);

    $this->assertFalse($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));

    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, null, null, true, 'approves leaves for');
    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, null, null, true, 'manages leaves for');

    $this->assertTrue($this->leaveManagerService->isContactManagedBy($this->staffMember['id'], $this->manager['id']));
  }

  public function testCurrentUserIsLeaveManagerOf() {
    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($this->staffMember['id']));

    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember);

    $this->assertTrue($this->leaveManagerService->currentUserIsLeaveManagerOf($this->staffMember['id']));
  }

  public function testCurrentUserIsLeaveManagerOfWhenTheresNoActiveRelationshipForTheCurrentDate() {
    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($this->staffMember['id']));

    // Set a relationship in the past
    $startDate = new DateTime('-10 days');
    $endDate = new DateTime('-1 day');
    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, $startDate->format('Y-m-d'), $endDate->format('Y-m-d'));

    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($this->staffMember['id']));

    // Set a relationship in the future
    $startDate = new DateTime('+1 day');
    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, $startDate->format('Y-m-d'));

    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($this->staffMember['id']));
  }

  public function testCurrentUserIsLeaveManagerOfWhenTheCurrentRelationshipIsNotActive() {
    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($this->staffMember['id']));
    
    $this->setContactAsLeaveApproverOf($this->manager, $this->staffMember, null, null, false);

    $this->assertFalse($this->leaveManagerService->currentUserIsLeaveManagerOf($this->staffMember['id']));
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
}
