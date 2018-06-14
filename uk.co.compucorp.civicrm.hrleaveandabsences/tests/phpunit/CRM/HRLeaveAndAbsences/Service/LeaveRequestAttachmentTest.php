<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachment as LeaveRequestAttachmentService;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRightsService;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachmentTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachmentTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;


  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');

    $this->leaveContact = 1;
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 1;');
  }

  /**
   * @expectedException UnexpectedValueException
   * @expectedExceptionMessage You must either be an L&A admin or an approver to this leave request to be able to delete the attachment
   */
  public function testDeleteShouldThrowAnExceptionWhenLoggedInUserIsNotAnAdminOrLeaveApprover() {
    $contactID = 1;
    $params = $this->getDefaultLeaveRequestParams();
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);
    // Register contact in session and make sure that no permission is set
    $this->registerCurrentLoggedInContactInSession($contactID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $leaveManagerService = new LeaveManagerService();
    $leaveRequestRights = new LeaveRightsService($leaveManagerService);
    $leaveRequestAttachmentService = new LeaveRequestAttachmentService($leaveRequestRights, $leaveManagerService);

    $attachment = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);
  }

  public function testDeleteShouldDeleteAttachmentWhenLoggedInUserIsAnAdmin() {
    $params = $this->getDefaultLeaveRequestParams();
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);
    $leaveRequestAttachmentService = $this->createLeaveRequestAttachmentsServiceWhenUserIsAdmin();

    $attachment = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $attachment2 = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);

    //confirm that two attachments exist for the leave request before deletion
    $attachmentList = $this->getAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $this->assertEquals($attachmentList['count'], 2);

    $result = $leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 0,
      'values' => [],
    ];
    $this->assertEquals($expected, $result);

    $attachmentList = $this->getAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $this->assertEquals($attachmentList['count'], 1);
    $this->assertEquals($attachmentList['values'][0]['id'], $attachment2['id']);
  }

  public function testDeleteShouldDeleteAttachmentWhenLoggedInUserIsTheLeaveApprover() {
    $leaveContact = ContactFabricator::fabricate();
    $params = $this->getDefaultLeaveRequestParams(['contact_id' => $leaveContact['id']]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $leaveManagerService = $this->getLeaveManagerServiceWhenUserIsLeaveApprover($leaveContact['id']);
    $leaveRequestRights = new LeaveRightsService($leaveManagerService);
    $leaveRequestAttachmentService = new LeaveRequestAttachmentService($leaveRequestRights, $leaveManagerService);

    $attachment = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);

    //confirm that only one attachment exist for the leave request before deletion
    $attachmentList = $this->getAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $this->assertEquals($attachmentList['count'], 1);
    $result = $leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 0,
      'values' => [],
    ];

    $this->assertEquals($expected, $result);
    $attachmentList = $this->getAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $this->assertEquals($attachmentList['count'], 0);
  }

  public function testDeleteShouldThrowAnExceptionWhenAttachmentHasBeenDeletedBefore() {
    $params = $this->getDefaultLeaveRequestParams();
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);
    $leaveRequestAttachmentService = $this->createLeaveRequestAttachmentsServiceWhenUserIsAdmin();

    $attachment = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);

    $leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);

    //try delete attachment again
    $this->setExpectedException('InvalidArgumentException', 'Attachment does not exist or has been deleted already!');
    $leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Attachment does not exist or has been deleted already!
   */
  public function testDeleteShouldThrowAnExceptionWhenAttachmentDoesNotExist() {
    $leaveRequestID = 1;
    $leaveRequestAttachmentService = $this->createLeaveRequestAttachmentsServiceWhenUserIsAdmin();

    $leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequestID, 'attachment_id' => 1]);
  }

  private function getDefaultLeaveRequestParams($params = []) {
    $defaultParams =  [
      'contact_id' => $this->leaveContact,
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ];
    return array_merge($defaultParams, $params);
  }


  public function testGetReturnsLeaveAttachmentDataOnlyForContactAUserHasAccessTo() {
    $staff1 = 1;
    $staff2 = 2;
    $params1 = $this->getDefaultLeaveRequestParams(['contact_id' => $staff1]);
    $params2 = $this->getDefaultLeaveRequestParams(['contact_id' => $staff2]);

    //Create Leave requests
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation($params1);
    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation($params2);

    //create attachments for Leave requests
    $attachment1 = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest1->id]);
    $attachment2 = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest2->id]);


    //Register the staff1 in session
    $this->registerCurrentLoggedInContactInSession($staff1);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $leaveManagerService = new LeaveManagerService();
    $leaveRightsService = $this->prophesize(LeaveRightsService::class);

    // staff1 has access to own self alone
    $leaveRightsService->getLeaveContactsCurrentUserHasAccessTo()->willReturn([$staff1]);
    $leaveRequestAttachmentService = new LeaveRequestAttachmentService($leaveRightsService->reveal(), $leaveManagerService);

    $staff1Attachment = $leaveRequestAttachmentService->get(['entity_id' => $leaveRequest1->id, 'sequential' => 1]);
    $staff2Attachment = $leaveRequestAttachmentService->get(['entity_id' => $leaveRequest2->id, 'sequential' => 1]);

    //result would be empty for contact user does not have access to
    $this->assertEmpty($staff2Attachment);

    $this->assertCount(1, $staff1Attachment['values']);
    $this->assertEquals($staff1Attachment['values'][0]['id'], $attachment1['id']);
    $this->assertEquals($staff1Attachment['values'][0]['name'], $attachment1['name']);
  }

  public function testGetReturnsLeaveAttachmentDataOnlyForAllContactsForAdmin() {
    $staff1 = 1;
    $staff2 = 2;
    $params1 = $this->getDefaultLeaveRequestParams(['contact_id' => $staff1]);
    $params2 = $this->getDefaultLeaveRequestParams(['contact_id' => $staff2]);

    //Create Leave requests
    $leaveRequest1 = LeaveRequestFabricator::fabricateWithoutValidation($params1);
    $leaveRequest2 = LeaveRequestFabricator::fabricateWithoutValidation($params2);

    //create attachments for Leave requests
    $attachment1 = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest1->id]);
    $attachment2 = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest2->id]);

    $leaveManagerService = $this->getLeaveManagerServiceWhenUserIsAdmin();
    $leaveRightsService = $this->prophesize(LeaveRightsService::class);
    $leaveRightsService->getLeaveContactsCurrentUserHasAccessTo()->willReturn([]);
    $leaveRequestAttachmentService = new LeaveRequestAttachmentService($leaveRightsService->reveal(), $leaveManagerService);

    $staff1Attachment = $leaveRequestAttachmentService->get(['entity_id' => $leaveRequest1->id, 'sequential' => 1]);
    $staff2Attachment = $leaveRequestAttachmentService->get(['entity_id' => $leaveRequest2->id, 'sequential' => 1]);

    //Admin is able to access attachments for all contacts
    $this->assertCount(1, $staff1Attachment['values']);
    $this->assertEquals($staff1Attachment['values'][0]['id'], $attachment1['id']);
    $this->assertEquals($staff1Attachment['values'][0]['name'], $attachment1['name']);

    $this->assertCount(1, $staff2Attachment['values']);
    $this->assertEquals($staff2Attachment['values'][0]['id'], $attachment2['id']);
    $this->assertEquals($staff2Attachment['values'][0]['name'], $attachment2['name']);
  }

  private function getLeaveManagerService($isAdmin, $leaveContact = NULL) {
    $leaveManagerService = $this->prophesize(LeaveManagerService::class);
    $leaveManagerService->currentUserIsAdmin()->willReturn($isAdmin);

    if ($leaveContact) {
      $leaveManagerService->currentUserIsLeaveManagerOf($leaveContact)->willReturn(TRUE);
    }

    return $leaveManagerService->reveal();
  }

  private function getLeaveManagerServiceWhenUserIsAdmin() {
    return $this->getLeaveManagerService(TRUE);
  }

  private function getLeaveManagerServiceWhenUserIsLeaveApprover($leaveContact) {
   return $this->getLeaveManagerService(FALSE, $leaveContact);
  }

  private function createLeaveRequestAttachmentsServiceWhenUserIsAdmin() {
    $leaveManagerService = $this->getLeaveManagerServiceWhenUserIsAdmin();
    $leaveRequestRights = new LeaveRightsService($leaveManagerService);
    $leaveRequestAttachmentService = new LeaveRequestAttachmentService($leaveRequestRights, $leaveManagerService);

    return $leaveRequestAttachmentService;
  }
}
