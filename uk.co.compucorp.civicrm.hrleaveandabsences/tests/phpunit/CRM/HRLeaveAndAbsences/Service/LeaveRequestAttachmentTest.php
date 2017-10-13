<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachment as LeaveRequestAttachmentService;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachmentTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachmentTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;

  private $leaveRequestAttachmentService;

  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');

    $this->leaveRequestAttachmentService = new LeaveRequestAttachmentService();
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

    $attachment = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $this->leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);
  }

  public function testDeleteShouldDeleteAttachmentWhenLoggedInUserIsAnAdmin() {
    $adminID = 2;
    $params = $this->getDefaultLeaveRequestParams();
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    // Register contact in session and set permission to admin
    $this->registerCurrentLoggedInContactInSession($adminID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $attachment = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $attachment2 = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);

    //confirm that two attachments exist for the leave request before deletion
    $attachmentList = $this->getAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $this->assertEquals($attachmentList['count'], 2);

    $result = $this->leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);

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
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();
    $params = $this->getDefaultLeaveRequestParams(['contact_id' => $leaveContact['id']]);
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    // Set logged in user as manager of Contact who requested leave
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $attachment = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);

    //confirm that only one attachment exist for the leave request before deletion
    $attachmentList = $this->getAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);
    $this->assertEquals($attachmentList['count'], 1);
    $result = $this->leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);

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
    $adminID = 2;
    $params = $this->getDefaultLeaveRequestParams();
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation($params);

    // Register contact in session and set permission to admin
    $this->registerCurrentLoggedInContactInSession($adminID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $attachment = $this->createAttachmentForLeaveRequest(['entity_id' => $leaveRequest->id]);

    $this->leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);

    //try delete attachment again
    $this->setExpectedException('InvalidArgumentException', 'Attachment does not exist or has been deleted already!');
    $this->leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequest->id, 'attachment_id' => $attachment['id']]);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Attachment does not exist or has been deleted already!
   */
  public function testDeleteShouldThrowAnExceptionWhenAttachmentDoesNotExist() {
    $adminID = 2;
    $leaveRequestID = 1;

    // Register contact in session and set permission to admin
    $this->registerCurrentLoggedInContactInSession($adminID);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $this->leaveRequestAttachmentService->delete(['leave_request_id' => $leaveRequestID, 'attachment_id' => 1]);
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
}
