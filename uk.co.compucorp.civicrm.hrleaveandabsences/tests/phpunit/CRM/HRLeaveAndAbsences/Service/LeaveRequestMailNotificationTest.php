<?php

use CRM_HRLeaveAndAbsences_Factory_LeaveRequestMailNotificationService as LeaveRequestMailNotificationService;
use CRM_HRLeaveAndAbsences_BAO_NotificationReceiver as NotificationReceiver;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;


/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotificationTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;


  private $leaveRequestMailNotification;

  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
    $this->leaveRequestMailNotification = LeaveRequestMailNotificationService::create();

    $this->leaveContact = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Staff1', 'last_name' => 'Staff1'], 'staffmember@dummysite.com'
    );
    $this->leaveRequestStatuses = $this->getLeaveRequestStatuses();
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
  }

  public function testGetRecipientEmailsReturnsCorrectlyWhenLeaveContactHasLeaveApprover() {
    $manager1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager1', 'last_name' => 'Manager1'], 'manager1@dummysite.com'
    );
    $manager2 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager2', 'last_name' => 'Manager2'], 'manager2@dummysite.com'
    );
    $manager3 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager3', 'last_name' => 'Manager3'], 'manager3@dummysite.com'
    );

    $this->setLeaveApproverRelationshipTypes(['has Leaves Approved By']);

    // Set manager1 and manager2 only to be leave aprovers for the leave contact
    $this->setContactAsLeaveApproverOf($manager1, $this->leaveContact, null, null, true, 'has Leaves Approved By');
    $this->setContactAsLeaveApproverOf($manager2, $this->leaveContact, null, null, true, 'has Leaves Approved By');

    $leaveRequest = LeaveRequest::create([
      'type_id' => 1,
      'contact_id' => $this->leaveContact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date_type' => 1,
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], false);

    $recipientEmails = $this->leaveRequestMailNotification->getRecipientEmails($leaveRequest);

    $this->assertCount(3, $recipientEmails);

    //The contact and the leave approvers are eligible recipients for this email notification.
    foreach($recipientEmails as $value) {
      $this->assertContains($value['email'], [
        'staffmember@dummysite.com', 'manager1@dummysite.com', 'manager2@dummysite.com'
      ]);

      $this->assertContains($value['api.Contact.get']['values'][0]['display_name'], [
        'Manager1 Manager1', 'Staff1 Staff1', 'Manager2 Manager2'
      ]);
    }
  }

  public function testGetRecipientEmailsReturnsCorrectlyWhenLeaveContactHasNoLeaveApproverButThereAreDefaultLeaveApproversForTheAbsenceType() {
    $defaultApprover1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Approver1', 'last_name' => 'Approver1'], 'approver1@dummysite.com'
    );
    $defaultApprover2 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Approver2', 'last_name' => 'Approver2'], 'approver2@dummysite.com'
    );

    $absenceType = 1;
    //add two default leave approvers for the absence type
    NotificationReceiver::addReceiversToAbsenceType($absenceType, [$defaultApprover1['id'], $defaultApprover2['id']]);

    $leaveRequest = LeaveRequest::create([
      'type_id' => 1,
      'contact_id' => $this->leaveContact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date_type' => 1,
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], false);

    $recipientEmails = $this->leaveRequestMailNotification->getRecipientEmails($leaveRequest);

    $this->assertCount(3, $recipientEmails);

    //The contact and default leave approvers for the absence type are eligible recipients for this email notification.
    foreach($recipientEmails as $value) {
      $this->assertContains($value['email'], [
        'staffmember@dummysite.com', 'approver1@dummysite.com', 'approver2@dummysite.com'
      ]);

      $this->assertContains($value['api.Contact.get']['values'][0]['display_name'], [
        'Approver1 Approver1', 'Staff1 Staff1', 'Approver2 Approver2'
      ]);
    }
  }

  public function testGetRecipientEmailsReturnsEmailsForContactAndLeaveApproverOnlyWhenLeaveContactHasLeaveApproverAndThereIsDefaultLeaveApproverForTheAbsenceType() {
    $manager1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager1', 'last_name' => 'Manager1'], 'manager1@dummysite.com'
    );

    $this->setLeaveApproverRelationshipTypes(['has Leaves Approved By']);

    // Set manager1 to be leave aprovers for the leave contact
    $this->setContactAsLeaveApproverOf($manager1, $this->leaveContact, null, null, true, 'has Leaves Approved By');

    $defaultApprover1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Approver1', 'last_name' => 'Approver1'], 'approver1@dummysite.com'
    );

    $absenceType = 1;
    //add a default leave approvers for the absence type
    NotificationReceiver::addReceiversToAbsenceType($absenceType, [$defaultApprover1['id']]);

    $leaveRequest = LeaveRequest::create([
      'type_id' => $absenceType,
      'contact_id' => $this->leaveContact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date_type' => 1,
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], false);

    $recipientEmails = $this->leaveRequestMailNotification->getRecipientEmails($leaveRequest);

    $this->assertCount(2, $recipientEmails);

    //since there are leave approvers for the leave contact, no mails will be sent to the default approvers
    //for the absence type
    foreach($recipientEmails as $value) {
      $this->assertContains($value['email'], [
        'staffmember@dummysite.com', 'manager1@dummysite.com'
      ]);

      $this->assertContains($value['api.Contact.get']['values'][0]['display_name'], [
        'Manager1 Manager1', 'Staff1 Staff1'
      ]);
    }
  }

  public function testGetTemplateReturnsTheCorrectTemplateForEachLeaveRequestType() {
    $leaveRequest = new LeaveRequest();

    $leaveRequest->request_type = LeaveRequest::REQUEST_TYPE_LEAVE;
    $leaveTemplate = $this->leaveRequestMailNotification->getTemplate($leaveRequest);
    $this->assertInstanceOf('CRM_Core_DAO_MessageTemplate', $leaveTemplate);
    $this->assertEquals($leaveTemplate->msg_title, 'CiviHR Leave Request Notification');

    $leaveRequest->request_type = LeaveRequest::REQUEST_TYPE_TOIL;
    $toilTemplate = $this->leaveRequestMailNotification->getTemplate($leaveRequest);
    $this->assertInstanceOf('CRM_Core_DAO_MessageTemplate', $toilTemplate);
    $this->assertEquals($toilTemplate->msg_title, 'CiviHR TOIL Request Notification');

    $leaveRequest->request_type = LeaveRequest::REQUEST_TYPE_SICKNESS;
    $sicknessTemplate = $this->leaveRequestMailNotification->getTemplate($leaveRequest);
    $this->assertInstanceOf('CRM_Core_DAO_MessageTemplate', $sicknessTemplate);
    $this->assertEquals($sicknessTemplate->msg_title, 'CiviHR Sickness Record Notification');
  }

  public function testGetTemplateParametersReturnsTheExpectedParametersForTheTemplate() {
    $leaveRequest = LeaveRequest::create([
      'type_id' => 1,
      'contact_id' => 2,
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'from_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date_type' => $this->leaveRequestDayTypes['All Day']['value'],
      'sickness_reason' => 1,
      'sickness_required_documents' => 1,
      'request_type' => LeaveRequest::REQUEST_TYPE_SICKNESS
    ], false);

    //create 3 attachments for leaveRequest
    $attachment1 = $this->createAttachmentForLeaveRequest([
      'entity_id' => $leaveRequest->id,
      'name' => 'LeaveRequestSampleFile1.txt'
    ]);

    $attachment2 = $this->createAttachmentForLeaveRequest([
      'entity_id' => $leaveRequest->id,
      'name' => 'LeaveRequestSampleFile2.txt'
    ]);

    //add one comment for the leave request
    $params = [
      'leave_request_id' => $leaveRequest->id,
      'text' => 'Random Commenter',
      'contact_id' => $leaveRequest->contact_id,
      'sequential' => 1
    ];

    $leaveRequestCommentService = new LeaveRequestCommentService();
    $leaveRequestCommentService->add($params);

    $tplParams = $this->leaveRequestMailNotification->getTemplateParameters($leaveRequest);

    $leaveRequestDayTypes = LeaveRequest::buildOptions('from_date_type');
    $leaveRequestStatuses = LeaveRequest::buildOptions('status_id');
    $sicknessReasons = LeaveRequest::buildOptions('sickness_reason');
    $fromDate = new DateTime($leaveRequest->from_date);
    $toDate = new DateTime($leaveRequest->to_date);

    //validate template parameters
    $this->assertEquals($tplParams['toDate'], $toDate->format('Y-m-d'));
    $this->assertEquals($tplParams['fromDate'], $fromDate->format('Y-m-d'));
    $this->assertEquals($tplParams['leaveRequest'], $leaveRequest);
    $this->assertEquals($tplParams['leaveRequestStatuses'], $leaveRequestStatuses);
    $this->assertEquals($tplParams['fromDateType'], $leaveRequestDayTypes[$leaveRequest->from_date_type]);
    $this->assertEquals($tplParams['toDateType'], $leaveRequestDayTypes[$leaveRequest->to_date_type]);
    $this->assertEquals($tplParams['leaveStatus'], $leaveRequestStatuses[$leaveRequest->status_id]);
    $this->assertEquals($tplParams['leaveRequestLink'], CRM_Utils_System::url('my-leave#/my-leave/report', [], true));

    //There are two attachments for the leave request
    $this->assertCount(2, $tplParams['leaveFiles']);
    foreach($tplParams['leaveFiles'] as $file) {
      $this->assertContains($file['name'], [
        'LeaveRequestSampleFile1.txt', 'LeaveRequestSampleFile2.txt'
      ]);
    }

    //there is one comment for the leave request
    $this->assertCount(1, $tplParams['leaveComments']);
    foreach($tplParams['leaveComments'] as $comment) {
      $this->assertEquals($comment['text'], 'Random Commenter');
      $this->assertEquals($comment['leave_request_id'], $leaveRequest->id);
    }

    //these parameters are only available for sickness requests
    $this->assertEquals($tplParams['sicknessReasons'], $sicknessReasons);
    $this->assertEquals($tplParams['sicknessRequiredDocuments'], $this->getSicknessRequiredDocuments());
    $this->assertEquals($tplParams['leaveRequiredDocuments'], explode(',', $leaveRequest->sickness_required_documents));
  }

  public function testSendEmailRetunsTrue() {
    $manager1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager1', 'last_name' => 'Manager1'], 'manager1@dummysite.com'
    );
    $manager2 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager2', 'last_name' => 'Manager2'], 'manager2@dummysite.com'
    );

    $this->setLeaveApproverRelationshipTypes(['has Leaves Approved By']);

    // Set manager1 and manager2 only to be leave aprovers for the leave contact
    $this->setContactAsLeaveApproverOf($manager1, $this->leaveContact, null, null, true, 'has Leaves Approved By');
    $this->setContactAsLeaveApproverOf($manager2, $this->leaveContact, null, null, true, 'has Leaves Approved By');

    $leaveRequest = LeaveRequest::create([
      'type_id' => 1,
      'contact_id' => $this->leaveContact['id'],
      'status_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'from_date_type' => 1,
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date_type' => 1,
      'toil_to_accrue' => 2,
      'toil_duration' => 120,
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE
    ], false);

    $mailStatus = $this->leaveRequestMailNotification->send($leaveRequest);

    $this->assertCount(3, $mailStatus);

    //since the emails are redirected to the database, the status is expected to be true for each email address
    foreach($mailStatus as $email => $status) {
      $this->assertContains($email, ['staffmember@dummysite.com', 'manager1@dummysite.com', 'manager2@dummysite.com']);
      $this->assertTrue($status);
    }
  }
}
