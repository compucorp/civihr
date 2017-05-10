<?php

use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix as LeaveRequestStatusMatrixService;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrixTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrixTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix
   */
  private $leaveRequestStatusMatrix;

  private $contactID;

  public function setUp() {
    $leaveManagerService = new  LeaveManagerService();
    $this->leaveRequestStatusMatrix = new LeaveRequestStatusMatrixService($leaveManagerService);

    $this->contactID = 1;
    $this->registerCurrentLoggedInContactInSession($this->contactID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];
  }

  /**
   * @dataProvider allPossibleStatusTransitionForStaffDataProvider
   */
  public function testCanTransitionToForStaffReturnsTrueForAllPossibleTransitionStatuses($fromStatus, $toStatus) {
    $this->assertTrue($this->leaveRequestStatusMatrix->canTransitionTo($fromStatus, $toStatus, $this->contactID));
  }

  /**
   * @dataProvider allNonPossibleStatusTransitionForStaffDataProvider
   */
  public function testCanTransitionToForStaffReturnsFalseForAllNonPossibleTransitionStatuses($fromStatus, $toStatus) {
    $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($fromStatus, $toStatus, $this->contactID));
  }

  public function testCanTransitionToForLeaveApproverReturnsTrueForAllPossibleTransitionStatuses() {
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);

    $possibleTransitions = $this->allPossibleStatusTransitionForLeaveApprover();

    foreach($possibleTransitions as $transition) {
      $this->assertTrue($this->leaveRequestStatusMatrix->canTransitionTo($transition[0], $transition[1], $leaveContact['id']));
    }
  }

  public function testCanTransitionToForLeaveApproverReturnsFalseForAllNonPossibleTransitionStatuses() {
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);

    $nonPossibleTransitions = $this->allNonPossibleStatusTransitionForLeaveApprover();

    foreach($nonPossibleTransitions as $transition) {
      $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($transition[0], $transition[1], $leaveContact['id']));
    }
  }

  public function testCanTransitionToForAdminForReturnsTrueAllPossibleTransitionStatuses() {
    $adminID = 5;
    $leaveContact = 2;
    $this->registerCurrentLoggedInContactInSession($adminID);
    $this->setPermissions(['administer leave and absences']);

    $possibleTransitions = $this->allPossibleStatusTransitionForLeaveApprover();

    foreach($possibleTransitions as $transition) {
      $this->assertTrue($this->leaveRequestStatusMatrix->canTransitionTo($transition[0], $transition[1], $leaveContact));
    }
  }

  public function testCanTransitionToForAdminReturnsFalseForAllNonPossibleTransitionStatuses() {
    $adminID = 5;
    $leaveContact = 2;
    $this->registerCurrentLoggedInContactInSession($adminID);
    $this->setPermissions(['administer leave and absences']);

    $nonPossibleTransitions = $this->allNonPossibleStatusTransitionForLeaveApprover();

    foreach($nonPossibleTransitions as $transition) {
      $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($transition[0], $transition[1], $leaveContact));
    }
  }

  public function testCanTransitionToReturnsTrueForAllPossibleStaffTransitionStatusesWhenLeaveApproverIsTheLeaveContact() {
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);

    $possibleTransitions = $this->allPossibleStatusTransitionForStaffDataProvider();

    foreach($possibleTransitions as $transition) {
      $this->assertTrue($this->leaveRequestStatusMatrix->canTransitionTo($transition[0], $transition[1], $manager['id']));
    }
  }

  public function testCanTransitionToReturnsFalseForAllNonPossibleStaffTransitionStatusesWhenLeaveApproverIsTheLeaveContact() {
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);

    $nonPossibleTransitions = $this->allNonPossibleStatusTransitionForStaffDataProvider();

    foreach($nonPossibleTransitions as $transition) {
      $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($transition[0], $transition[1], $manager['id']));
    }
  }

  public function testCanTransitionToReturnsFalseForPossibleManagerExclusiveStatusTransitionsWhenLeaveApproverIsTheLeaveContact() {
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);

    $managerExclusivePossibleStatusTransition = $this->getManagerExclusivePossibleStatusTransitionsDataProvider();
    foreach($managerExclusivePossibleStatusTransition as $transition) {
      $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($transition[0], $transition[1], $manager['id']));
    }
  }

  /**
   * @dataProvider allPossibleStatusTransitionForStaffDataProvider
   */
  public function testCanTransitionToReturnsTrueForAllPossibleStaffTransitionStatusesWhenAdminIsTheLeaveContact($fromStatus, $toStatus) {
    $adminID = 5;
    $this->registerCurrentLoggedInContactInSession($adminID);
    $this->setPermissions(['administer leave and absences']);

    $this->assertTrue($this->leaveRequestStatusMatrix->canTransitionTo($fromStatus, $toStatus, $adminID));
  }

  /**
   * @dataProvider allNonPossibleStatusTransitionForStaffDataProvider
   */
  public function testCanTransitionToReturnsFalseForAllNonPossibleStaffTransitionStatusesWhenAdminIsTheLeaveContact($fromStatus, $toStatus) {
    $adminID = 5;
    $this->registerCurrentLoggedInContactInSession($adminID);
    $this->setPermissions(['administer leave and absences']);

    $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($fromStatus, $toStatus, $adminID));
  }

  /**
   * @dataProvider getManagerExclusivePossibleStatusTransitionsDataProvider
   */
  public function testCanTransitionToReturnsFalseForPossibleManagerExclusiveStatusTransitionsWhenAdminIsTheLeaveContact($fromStatus, $toStatus) {
    $adminID = 5;
    $this->registerCurrentLoggedInContactInSession($adminID);
    $this->setPermissions(['administer leave and absences']);

    $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($fromStatus, $toStatus, $adminID));
  }

  public function allPossibleStatusTransitionForStaffDataProvider() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    return [
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['cancelled']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['cancelled']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['cancelled']],
      ['', $leaveRequestStatuses['waiting_approval']],
    ];
  }

  public function allNonPossibleStatusTransitionForStaffDataProvider() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    return [
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['cancelled']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['cancelled']],
      ['', $leaveRequestStatuses['more_information_requested']],
      ['', $leaveRequestStatuses['rejected']],
      ['', $leaveRequestStatuses['approved']],
      ['', $leaveRequestStatuses['cancelled']],
    ];
  }

  public function allPossibleStatusTransitionForLeaveApprover() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    return [
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['cancelled']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['cancelled']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['cancelled']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['cancelled']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['more_information_requested']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['rejected']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['approved']],
      [$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['cancelled']],
      ['', $leaveRequestStatuses['more_information_requested']],
      ['', $leaveRequestStatuses['approved']],
    ];
  }

  public function allNonPossibleStatusTransitionForLeaveApprover() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));

    return [
      [$leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['rejected'], $leaveRequestStatuses['waiting_approval']],
      [$leaveRequestStatuses['approved'], $leaveRequestStatuses['waiting_approval']],
      ['', $leaveRequestStatuses['waiting_approval']],
      ['', $leaveRequestStatuses['rejected']],
      ['', $leaveRequestStatuses['cancelled']],
    ];
  }

  /**
   * Return possible status transitions that is only exclusive to the Manager or Admin
   *
   * @return array
   */
  public function getManagerExclusivePossibleStatusTransitionsDataProvider() {
    $possibleStaffTransitions = $this->allPossibleStatusTransitionForStaffDataProvider();
    $possibleManagerTransitions = $this->allPossibleStatusTransitionForLeaveApprover();

    $results = array_diff(array_map('serialize', $possibleManagerTransitions), array_map('serialize', $possibleStaffTransitions));
    $managerExclusiveStatusTransition = array_map('unserialize', $results);

    return $managerExclusiveStatusTransition;
  }
}
