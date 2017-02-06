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
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $possibleTransitions = $this->allPossibleStatusTransitionForLeaveApprover();

    foreach($possibleTransitions as $transition) {
      $this->assertTrue($this->leaveRequestStatusMatrix->canTransitionTo($transition[0], $transition[1], $leaveContact['id']));
    }
  }

  public function testCanTransitionToForAdminReturnsFalseForAllNonPossibleTransitionStatuses() {
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $nonPossibleTransitions = $this->allNonPossibleStatusTransitionForLeaveApprover();

    foreach($nonPossibleTransitions as $transition) {
      $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($transition[0], $transition[1], $leaveContact['id']));
    }
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
}
