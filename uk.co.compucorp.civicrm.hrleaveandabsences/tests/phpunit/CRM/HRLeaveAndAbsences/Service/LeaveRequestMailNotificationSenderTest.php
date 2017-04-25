<?php

use  CRM_HRLeaveAndAbsences_Mail_Message as Message;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotificationSender as LeaveRequestMailNotificationSenderService;
use CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate as RequestNotificationTemplateFactory;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;


/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotificationSenderTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotificationSenderTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;


  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

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

    $leaveRequestTemplateFactory = new RequestNotificationTemplateFactory();
    $message = new Message($leaveRequest, $leaveRequestTemplateFactory);

    $leaveMailSenderService = new LeaveRequestMailNotificationSenderService();

    $mailStatus = $leaveMailSenderService->send($message);

    //The email was sent to the leave contact and leave approver
    $this->assertCount(2, $mailStatus);

    //since the emails are redirected to the database, the status is expected to be true for each email address
    foreach($mailStatus as $email => $status) {
      $this->assertContains($email, ['staffmember@dummysite.com', 'manager1@dummysite.com']);
      $this->assertTrue($status);
    }
  }
}
