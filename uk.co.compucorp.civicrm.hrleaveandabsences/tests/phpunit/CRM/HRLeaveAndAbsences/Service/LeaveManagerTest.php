<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveManagerTest extends BaseHeadlessTest {

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

  private function setContactAsLeaveApproverOf($leaveApprover, $contact, $startDate = null, $endDate = null, $isActive = true) {
    $relationshipType = $this->getLeaveApproverRelationshipType();

    civicrm_api3('Relationship', 'create', array(
      'sequential' => 1,
      'contact_id_a' => $contact['id'],
      'contact_id_b' => $leaveApprover['id'],
      'relationship_type_id' => $relationshipType['id'],
      'start_date' => $startDate,
      'end_date' => $endDate,
      'is_active' => $isActive
    ));
  }

  private function getLeaveApproverRelationshipType() {
    $result = civicrm_api3('RelationshipType', 'get', [
      'name_a_b' => 'has Leave Approved by',
    ]);

    if(empty($result['values'])) {
      return $this->createLeaveApproverRelationshipType();
    }

    return array_shift($result['values']);
  }

  private function createLeaveApproverRelationshipType() {
    $result = civicrm_api3('RelationshipType', 'create', array(
      'sequential'     => 1,
      'name_a_b'       => 'has Leave Approved by',
      'name_b_a'       => 'is Leave Approver of',
      'contact_type_a' => 'Individual',
      'contact_type_b' => 'Individual',
    ));

    return $result['values'][0];
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
