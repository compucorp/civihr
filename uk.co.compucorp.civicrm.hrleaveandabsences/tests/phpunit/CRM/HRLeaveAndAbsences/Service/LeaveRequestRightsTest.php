<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_AbsenceType as AbsenceTypeFabricator;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

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
    $managerId = 5;
    $this->registerCurrentLoggedInContactInSession($managerId);
    $this->assertFalse($this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canDeleteFor($this->leaveContact));
    $this->unregisterCurrentLoggedInContactFromSession();
  }

  public function testCanDeleteForReturnsTrueWhenCurrentUserIsAdmin() {
    $this->assertTrue($this->getLeaveRequestRightsForAdminAsCurrentUser()->canDeleteFor($this->leaveContact));
  }

  public function testCanDeleteForReturnsTrueWhenCurrentUserIsOwnLeaveApproverAndIsOwnRequest() {
    $this->registerCurrentLoggedInContactInSession($this->leaveContact);
    $this->assertTrue($this->getLeaveRequestRightsForLeaveManagerAsCurrentUser()->canDeleteFor($this->leaveContact));
    $this->unregisterCurrentLoggedInContactFromSession();
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

  public function testCanCancelToilWithPastDatesReturnsTrueWhenCurrentUserIsManagerAndAbsenceTypeDoesNotAllowPastAccrual() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => TRUE,
      'allow_accrue_in_the_past' => FALSE
    ]);

    $leaveRightsService = $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser();
    $this->assertTrue($leaveRightsService->canCancelToilWithPastDates($this->leaveContact, $absenceType->id));
  }

  public function testCanCancelToilWithPastDatesReturnsTrueWhenCurrentUserIsAdminAndAbsenceTypeDoesNotAllowPastAccrual() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => TRUE,
      'allow_accrue_in_the_past' => FALSE
    ]);

    $leaveRightsService = $this->getLeaveRequestRightsForAdminAsCurrentUser();
    $this->assertTrue($leaveRightsService->canCancelToilWithPastDates($this->leaveContact, $absenceType->id));
  }

  public function testCanCancelToilWithPastDatesReturnsFalseWhenCurrentUserIsLeaveContactAndAbsenceTypeDoesNotAllowPastAccrual() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => TRUE,
      'allow_accrue_in_the_past' => FALSE
    ]);

    $leaveRightsService = $this->getLeaveRightsService();
    $this->assertFalse($leaveRightsService->canCancelToilWithPastDates($this->leaveContact, $absenceType->id));
  }

  public function testCanCancelToilWithPastDatesReturnsTrueWhenAbsenceTypeAllowsPastAccrualForLeaveContact() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => TRUE,
      'allow_accrue_in_the_past' => TRUE
    ]);

    $leaveRightsService = $this->getLeaveRightsService();
    $this->assertTrue($leaveRightsService->canCancelToilWithPastDates($this->leaveContact, $absenceType->id));
  }

  public function testCanCancelToilWithPastDatesReturnsTrueWhenAbsenceTypeAllowsPastAccrualForAdmin() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => TRUE,
      'allow_accrue_in_the_past' => TRUE
    ]);

    $leaveRightsService = $this->getLeaveRequestRightsForAdminAsCurrentUser();
    $this->assertTrue($leaveRightsService->canCancelToilWithPastDates($this->leaveContact, $absenceType->id));
  }

  public function testCanCancelToilWithPastDatesReturnsTrueWhenAbsenceTypeAllowsPastAccrualForManager() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_accruals_request' => TRUE,
      'allow_accrue_in_the_past' => TRUE
    ]);

    $leaveRightsService = $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser();
    $this->assertTrue($leaveRightsService->canCancelToilWithPastDates($this->leaveContact, $absenceType->id));
  }

  public function testStaffMembersShouldOnlyHaveAccessToThemselves() {
    $staffMember1 = ContactFabricator::fabricate();
    $staffMember2 = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($staffMember1['id']);
    $leaveRequestRightsService = $this->getLeaveRightsService();
    $accessibleContacts = $leaveRequestRightsService->getLeaveContactsCurrentUserHasAccessTo();
    $this->assertEquals([$staffMember1['id']], $accessibleContacts);
  }

  public function testGetLeaveApproverShouldOnlyHaveAccessToManagees() {
    $manager = ContactFabricator::fabricate();
    $staffMember1 = ContactFabricator::fabricate();
    $staffMember2 = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $staffMember2);
    $leaveRequestRightsService = $this->getLeaveRightsService();
    $accessibleContacts = $leaveRequestRightsService->getLeaveContactsCurrentUserHasAccessTo();
    sort($accessibleContacts);
    //The leave approver has access to his own contact id and that of his managees.
    $this->assertEquals([$manager['id'],$staffMember2['id']], $accessibleContacts);
  }

  public function testAdminShouldHaveAccessToAllContacts() {
    $staffMember1 = ContactFabricator::fabricate();
    $staffMember2 = ContactFabricator::fabricate();
    $leaveRequestRightsService = $this->getLeaveRequestRightsForAdminAsCurrentUser();
    $accessibleContacts = $leaveRequestRightsService->getLeaveContactsCurrentUserHasAccessTo();
    //In reality, An admin user has access to all contacts, but an empty array is returned in
    //this case.
    $this->assertEquals([], $accessibleContacts);
  }

  public function testCanCancelForAbsenceTypeReturnsTrueWhenUserIsAdmin() {
    $typeId = 1;
    $contactID = 2;
    $leaveDate = new DateTime();
    $leaveRequestRightsService = $this->getLeaveRequestRightsForAdminAsCurrentUser();
    $result = $leaveRequestRightsService->canCancelForAbsenceType($typeId, $contactID, $leaveDate);
    $this->assertTrue($result);
  }

  public function testCanCancelForAbsenceTypeReturnsTrueWhenUserIsLeaveManager() {
    $typeId = 1;
    $contactID = 2;
    $leaveDate = new DateTime();
    $leaveRequestRightsService = $this->getLeaveRequestRightsForLeaveManagerAsCurrentUser();
    $result = $leaveRequestRightsService->canCancelForAbsenceType($typeId, $contactID, $leaveDate);
    $this->assertTrue($result);
  }

  public function testCanCancelForAbsenceTypeReturnsTrueWhenAbsenceTypeAllowsCancellationForStaff() {
    $absenceType = AbsenceTypeFabricator::fabricate([
      'allow_request_cancelation' => AbsenceType::REQUEST_CANCELATION_ALWAYS
    ]);
    $contactID = 2;
    $leaveDate = new DateTime();
    $leaveRequestRightsService = $this->getLeaveRightsService();
    $result = $leaveRequestRightsService->canCancelForAbsenceType($absenceType->id, $contactID, $leaveDate);
    $this->assertTrue($result);
  }

  public function testCanCancelForAbsenceTypeReturnsFalseWhenAbsenceTypeDoesNotAllowCancellationForStaff() {
    $absenceType = AbsenceTypeFabricator::fabricate(['allow_request_cancelation' => AbsenceType::REQUEST_CANCELATION_NO]);
    $contactID = 2;
    $leaveDate = new DateTime();
    $leaveRequestRightsService = $this->getLeaveRightsService();
    $result = $leaveRequestRightsService->canCancelForAbsenceType($absenceType->id, $contactID, $leaveDate);
    $this->assertFalse($result);
  }

  public function testCanCancelForAbsenceTypeReturnsFalseWhenAbsenceTypeAllowsCancellationForFutureDateButLeaveDateIsPast() {
    $absenceType = AbsenceTypeFabricator::fabricate(['allow_request_cancelation' => AbsenceType::REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE]);
    $contactID = 2;
    $leaveDate = new DateTime('yesterday');
    $leaveRequestRightsService = $this->getLeaveRightsService();
    $result = $leaveRequestRightsService->canCancelForAbsenceType($absenceType->id, $contactID, $leaveDate);
    $this->assertFalse($result);
  }

  public function testCanCancelForAbsenceTypeReturnsTrueWhenAbsenceTypeAllowsCancellationForFutureDateAndLeaveDateIsInFuture() {
    $absenceType = AbsenceTypeFabricator::fabricate(['allow_request_cancelation' => AbsenceType::REQUEST_CANCELATION_IN_ADVANCE_OF_START_DATE]);
    $contactID = 2;
    $leaveDate = new DateTime('tomorrow');
    $leaveRequestRightsService = $this->getLeaveRightsService();
    $result = $leaveRequestRightsService->canCancelForAbsenceType($absenceType->id, $contactID, $leaveDate);
    $this->assertTrue($result);
  }

  private function getLeaveRightsService($isAdmin = FALSE, $isManager = FALSE) {
    $leaveManagerService = $this->createLeaveManagerServiceMock($isAdmin, $isManager);
    return new LeaveRequestRightsService($leaveManagerService);
  }

  private function getLeaveRequestRightsForAdminAsCurrentUser() {
    return $this->getLeaveRightsService(TRUE, FALSE);
  }

  private function getLeaveRequestRightsForLeaveManagerAsCurrentUser() {
    return $this->getLeaveRightsService(FALSE, TRUE);
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
