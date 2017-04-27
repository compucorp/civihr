<?php

use CRM_HRLeaveAndAbsences_Mail_Message as Message;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotificationSender as LeaveRequestMailNotificationSenderService;
use CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate as RequestNotificationTemplateFactory;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;


/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotificationSenderTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotificationSenderTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_MailHelpersTrait;


  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');

    $this->leaveContact = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Staff1', 'last_name' => 'Staff1'], 'staffmember@dummysite.com'
    );
  }

  public function testSendRetunsTrue() {
    $manager1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager1', 'last_name' => 'Manager1'], 'manager1@dummysite.com'
    );

    $this->setLeaveApproverRelationshipTypes(['has Leaves Approved By']);

    // Set manager1 to be leave aprovers for the leave contact
    $this->setContactAsLeaveApproverOf($manager1, $this->leaveContact, null, null, true, 'has Leaves Approved By');

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' =>$this->leaveContact['id'],
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
    ], false);

    //delete emails sent when leave request is created
    $this->deleteEmailNotificationsInDatabase();
    $result = $this->getEmailNotificationsFromDatabase(['staffmember@dummysite.com', 'manager1@dummysite.com']);
    $this->assertEquals(0, $result->N);

    $leaveRequestTemplateFactory = new RequestNotificationTemplateFactory();
    $message = new Message($leaveRequest, $leaveRequestTemplateFactory);

    $leaveMailSenderService = new LeaveRequestMailNotificationSenderService();
    $leaveMailSenderService->send($message);

    //Check the message spool table for the emails
    $result = $this->getEmailNotificationsFromDatabase(['staffmember@dummysite.com', 'manager1@dummysite.com']);

    //To make sure that duplicate emails were not sent but one mail per recipient
    $this->assertEquals(2, $result->N);

    $emails = [];
    while($result->fetch()) {
      $emails[] = ['email' => $result->recipient_email, 'body' => $result->body, 'headers' => $result->headers];
    }

    $recipientEmails = array_column($emails, 'email');
    sort($recipientEmails);

    $expectedEmails = ['manager1@dummysite.com', 'staffmember@dummysite.com'];
    $this->assertEquals($recipientEmails, $expectedEmails);

    //check that the headers and body are not empty for the emails
    foreach($emails as $email) {
      $this->assertNotEmpty($email['body']);
      $this->assertNotEmpty($email['headers']);
    }
  }
}
