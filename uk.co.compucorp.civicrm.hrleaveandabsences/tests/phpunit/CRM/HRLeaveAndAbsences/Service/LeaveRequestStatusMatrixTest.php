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

  /**
   * @dataProvider allPossibleStatusTransitionForLeaveApproverDataProvider
   */
  public function testCanTransitionToForLeaveApproverReturnsTrueForAllPossibleTransitionStatuses($manager, $leaveContact, $fromStatus, $toStatus) {
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);

    $this->assertTrue($this->leaveRequestStatusMatrix->canTransitionTo($fromStatus, $toStatus, $leaveContact['id']));
  }

  /**
   * @dataProvider allNonPossibleStatusTransitionForLeaveApproverDataProvider
   */
  public function testCanTransitionToForLeaveApproverReturnsFalseForAllNonPossibleTransitionStatuses($manager, $leaveContact, $fromStatus, $toStatus) {
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);

    $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($fromStatus, $toStatus, $leaveContact['id']));
  }

  /**
   * @dataProvider allPossibleStatusTransitionForLeaveApproverDataProvider
   */
  public function testCanTransitionToForAdminForReturnsTrueAllPossibleTransitionStatuses($manager, $leaveContact, $fromStatus, $toStatus) {
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $this->assertTrue($this->leaveRequestStatusMatrix->canTransitionTo($fromStatus, $toStatus, $leaveContact['id']));
  }

  /**
   * @dataProvider allNonPossibleStatusTransitionForLeaveApproverDataProvider
   */
  public function testCanTransitionToForAdminReturnsFalseForAllNonPossibleTransitionStatuses($manager, $leaveContact, $fromStatus, $toStatus) {
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $this->assertFalse($this->leaveRequestStatusMatrix->canTransitionTo($fromStatus, $toStatus, $leaveContact['id']));
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

  public function allPossibleStatusTransitionForLeaveApproverDataProvider() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();

    return [
      [$manager, $leaveContact, $leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['more_information_requested']],
      [$manager, $leaveContact, $leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['rejected']],
      [$manager, $leaveContact, $leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['approved']],
      [$manager, $leaveContact, $leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['cancelled']],
      [$manager, $leaveContact, $leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['more_information_requested']],
      [$manager, $leaveContact, $leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['rejected']],
      [$manager, $leaveContact, $leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['approved']],
      [$manager, $leaveContact, $leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['cancelled']],
      [$manager, $leaveContact, $leaveRequestStatuses['rejected'], $leaveRequestStatuses['more_information_requested']],
      [$manager, $leaveContact, $leaveRequestStatuses['rejected'], $leaveRequestStatuses['rejected']],
      [$manager, $leaveContact, $leaveRequestStatuses['rejected'], $leaveRequestStatuses['approved']],
      [$manager, $leaveContact, $leaveRequestStatuses['rejected'], $leaveRequestStatuses['cancelled']],
      [$manager, $leaveContact, $leaveRequestStatuses['approved'], $leaveRequestStatuses['more_information_requested']],
      [$manager, $leaveContact, $leaveRequestStatuses['approved'], $leaveRequestStatuses['rejected']],
      [$manager, $leaveContact, $leaveRequestStatuses['approved'], $leaveRequestStatuses['approved']],
      [$manager, $leaveContact, $leaveRequestStatuses['approved'], $leaveRequestStatuses['cancelled']],
      [$manager, $leaveContact, $leaveRequestStatuses['cancelled'], $leaveRequestStatuses['waiting_approval']],
      [$manager, $leaveContact, $leaveRequestStatuses['cancelled'], $leaveRequestStatuses['more_information_requested']],
      [$manager, $leaveContact,$leaveRequestStatuses['cancelled'], $leaveRequestStatuses['rejected']],
      [$manager, $leaveContact, $leaveRequestStatuses['cancelled'], $leaveRequestStatuses['approved']],
      [$manager, $leaveContact, $leaveRequestStatuses['cancelled'], $leaveRequestStatuses['cancelled']],
      [$manager, $leaveContact, '', $leaveRequestStatuses['more_information_requested']],
      [$manager, $leaveContact, '', $leaveRequestStatuses['approved']],
    ];
  }

  public function allNonPossibleStatusTransitionForLeaveApproverDataProvider() {
    $leaveRequestStatuses = array_flip(LeaveRequest::buildOptions('status_id', 'validate'));
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();

    return [
      [$manager, $leaveContact, $leaveRequestStatuses['waiting_approval'], $leaveRequestStatuses['waiting_approval']],
      [$manager, $leaveContact, $leaveRequestStatuses['more_information_requested'], $leaveRequestStatuses['waiting_approval']],
      [$manager, $leaveContact, $leaveRequestStatuses['rejected'], $leaveRequestStatuses['waiting_approval']],
      [$manager, $leaveContact, $leaveRequestStatuses['approved'], $leaveRequestStatuses['waiting_approval']],
      [$manager, $leaveContact, '', $leaveRequestStatuses['waiting_approval']],
      [$manager, $leaveContact, '', $leaveRequestStatuses['rejected']],
      [$manager, $leaveContact, '', $leaveRequestStatuses['cancelled']],
    ];
  }
}
