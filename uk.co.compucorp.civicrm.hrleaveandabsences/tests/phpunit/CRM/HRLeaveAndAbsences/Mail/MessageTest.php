<?php

use CRM_HRLeaveAndAbsences_BAO_NotificationReceiver as NotificationReceiver;
use CRM_HRLeaveAndAbsences_Mail_Message as Message;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate as RequestNotificationTemplateFactory;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

/**
 * Class CRM_HRLeaveAndAbsences_Mail_MessageTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Mail_MessageTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_MailHelpersTrait;

  private $leaveRequestTemplateFactory;

  private $leaveContact;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $this->leaveRequestTemplateFactory = new RequestNotificationTemplateFactory();

    $this->leaveContact = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Staff1', 'last_name' => 'Staff1'], 'staffmember@dummysite.com'
    );
  }

  public function testGetRecipientEmailsReturnsCorrectlyWhenLeaveContactHasLeaveApprover() {
    $manager1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager1', 'last_name' => 'Manager1'], 'manager1@dummysite.com'
    );
    $manager2 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager2', 'last_name' => 'Manager2'], 'manager2@dummysite.com'
    );

    $managerService = $this->prophesize(LeaveManagerService::class);
    $returnValue = [
      $manager1['id'] => $manager1['first_name'],
      $manager2['id'] => $manager2['first_name']
    ];
    // Set manager1 and manager2 only to be leave aprovers for the leave contact
    $managerService->getLeaveApproversForContact($this->leaveContact['id'])->willReturn($returnValue);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => $this->leaveContact['id'],
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
    ], false);

    $message = new Message($leaveRequest, $this->leaveRequestTemplateFactory, $managerService->reveal());

    $recipientEmails = array_column($message->getRecipientEmails($leaveRequest), 'email');
    sort($recipientEmails);

    $this->assertCount(3, $recipientEmails);
    //The contact and the leave approvers are eligible recipients for this email notification.
    $expectedEmails = ['manager1@dummysite.com', 'manager2@dummysite.com', 'staffmember@dummysite.com'];
    $this->assertEquals($expectedEmails, $recipientEmails);
  }

  public function testGetRecipientEmailsReturnsCorrectlyWhenLeaveContactHasNoLeaveApproverButThereAreDefaultLeaveApproversForTheAbsenceType() {
    $defaultApprover1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Approver1', 'last_name' => 'Approver1'], 'approver1@dummysite.com'
    );
    $defaultApprover2 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Approver2', 'last_name' => 'Approver2'], 'approver2@dummysite.com'
    );

    $managerService = $this->prophesize(LeaveManagerService::class);
    $managerService->getLeaveApproversForContact($this->leaveContact['id'])->willReturn([]);

    $absenceType = 1;
    //add two default leave approvers for the absence type
    NotificationReceiver::addReceiversToAbsenceType($absenceType, [$defaultApprover1['id'], $defaultApprover2['id']]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' => $this->leaveContact['id'],
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
    ], false);

    $message = new Message($leaveRequest, $this->leaveRequestTemplateFactory, $managerService->reveal());

    $recipientEmails = array_column($message->getRecipientEmails($leaveRequest), 'email');
    sort($recipientEmails);

    $this->assertCount(3, $recipientEmails);
    //The contact and default leave approvers for the absence type are eligible recipients for this email notification.
    $expectedEmails = ['approver1@dummysite.com', 'approver2@dummysite.com', 'staffmember@dummysite.com'];
    $this->assertEquals($expectedEmails, $recipientEmails);
  }

  public function testGetRecipientEmailsReturnsEmailsForContactAndLeaveApproverOnlyWhenLeaveContactHasLeaveApproverAndThereIsDefaultLeaveApproverForTheAbsenceType() {
    $manager1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Manager1', 'last_name' => 'Manager1'], 'manager1@dummysite.com'
    );
    $managerService = $this->prophesize(LeaveManagerService::class);
    $returnValue = [
      $manager1['id'] => $manager1['first_name'],
    ];

    // Set manager1 to be leave aprovers for the leave contact
    $managerService->getLeaveApproversForContact($this->leaveContact['id'])->willReturn($returnValue);

    $defaultApprover1 = ContactFabricator::fabricateWithEmail([
      'first_name' => 'Approver1', 'last_name' => 'Approver1'], 'approver1@dummysite.com'
    );

    $absenceType = 1;
    //add a default leave approvers for the absence type
    NotificationReceiver::addReceiversToAbsenceType($absenceType, [$defaultApprover1['id']]);

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' =>$this->leaveContact['id'],
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
    ], false);

    $message = new Message($leaveRequest, $this->leaveRequestTemplateFactory, $managerService->reveal());
    $recipientEmails = array_column($message->getRecipientEmails($leaveRequest), 'email');
    sort($recipientEmails);

    $this->assertCount(2, $recipientEmails);
    //since there are leave approvers for the leave contact, no mails will be sent to the default approvers
    //for the absence type
    $expectedEmails = ['manager1@dummysite.com', 'staffmember@dummysite.com'];
    $this->assertEquals($expectedEmails, $recipientEmails);
  }

  public function testGetLeaveContact() {
    $leaveRequest = new LeaveRequest();
    $leaveRequest->contact_id = 2;
    $message = new Message($leaveRequest, $this->leaveRequestTemplateFactory, $this->getManagerService());
    $this->assertEquals($leaveRequest->contact_id, $message->getLeaveContactID());
  }

  public function testGetTemplateParametersForLeaveContact() {
    $leaveRequestId = 1;
    $templateParameters = [
      'status' => 'Mock Status',
      'date' => 'Test Date'
    ];
    $expectedUrl = ['leaveRequestLink' => CRM_Utils_System::url(
      'my-leave#/my-leave/report?leave-request-id=' . $leaveRequestId, [], true)];
    $expectedParameters = array_merge($templateParameters, $expectedUrl);
    $leaveTemplate = $this->createLeaveTemplateMock($templateParameters);
    $notificationTemplateFactory = $this->createRequestNotificationTemplateFactoryMock($leaveTemplate);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->id = $leaveRequestId;
    $leaveRequest->contact_id = 1;
    $message = new Message($leaveRequest, $notificationTemplateFactory, $this->getManagerService());
    $this->assertEquals($expectedParameters, $message->getTemplateParameters($leaveRequest->contact_id));
  }

  public function testGetTemplateParametersForManager() {
    $leaveRequestId = 1;
    $templateParameters = [
      'status' => 'Mock Status',
      'date' => 'Test Date'
    ];
    $expectedUrl = ['leaveRequestLink' => CRM_Utils_System::url(
      'manager-leave#/manager-leave/requests?leave-request-id=' . $leaveRequestId, [], true)];
    $expectedParameters = array_merge($templateParameters, $expectedUrl);
    $leaveTemplate = $this->createLeaveTemplateMock($templateParameters);
    $notificationTemplateFactory = $this->createRequestNotificationTemplateFactoryMock($leaveTemplate);

    $leaveRequest = new LeaveRequest();
    $leaveRequest->id = $leaveRequestId;
    $leaveRequest->contact_id = 1;
    $managerID = 2;
    $message = new Message($leaveRequest, $notificationTemplateFactory, $this->getManagerService());
    $this->assertEquals($expectedParameters, $message->getTemplateParameters($managerID));
  }

  public function testGetTemplateID() {
    $expectedTemplateID = 5;
    $leaveTemplate = $this->createLeaveTemplateMock([], $expectedTemplateID);
    $notificationTemplateFactory = $this->createRequestNotificationTemplateFactoryMock($leaveTemplate);

    $leaveRequest = new LeaveRequest();
    $message = new Message($leaveRequest, $notificationTemplateFactory, $this->getManagerService());
    $this->assertEquals($expectedTemplateID, $message->getTemplateID());
  }

  public function testGetFromEmailReturnsTheFirstOptionFromTheFromEmailAddressOptionGroupWhenThereIsNoDefaultAddress() {
    $fromEmailAddress = [
      'From Email 1 <from_email1@testdomain.com>',
      'From Email 2 <from_email2@testdomain.com>',
      'From Email 3 <from_email3@testdomain.com>',
    ];

    foreach ($fromEmailAddress as $fromAddress) {;
      $this->createFromEmail($fromAddress);
    }
    $leaveRequest = new LeaveRequest();
    $message = new Message($leaveRequest, $this->leaveRequestTemplateFactory, $this->getManagerService());
    $fromEmail = $message->getFromEmail();

    $this->assertEquals($fromEmailAddress[0], $fromEmail);
  }

  public function testGetFromEmailReturnsTheDefaultEmailAddressFromTheFromEmailAddressOption() {
    $fromEmailAddress = [
      'From Email 1 <from_email1@testdomain.com>',
      'From Email 2 <from_email2@testdomain.com>',
      'From Email 3 <from_email3@testdomain.com>',
    ];

    foreach ($fromEmailAddress as $fromAddress) {
      $this->createFromEmail($fromAddress);
    }

    $defaultEmailAddress = 'Default Email <default_email@testdomain.com>';
    $this->createDefaultFromEmail($defaultEmailAddress);

    $leaveRequest = new LeaveRequest();
    $message = new Message($leaveRequest, $this->leaveRequestTemplateFactory, $this->getManagerService());
    $fromEmail = $message->getFromEmail();

    $this->assertEquals($defaultEmailAddress, $fromEmail);
  }

  public function testGetFromEmailReturnsNullWhenThereIsNoOptionForTheFromEmailAddressOptionGroup() {
    $leaveRequest = new LeaveRequest();
    $message = new Message($leaveRequest, $this->leaveRequestTemplateFactory, $this->getManagerService());
    $fromEmail = $message->getFromEmail();

    $this->assertNull($fromEmail);
  }

  public function testGetTemplateParametersReturnsNullWhenThereIsNoTemplateForARequestType() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' =>$this->leaveContact['id'],
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'request_type' => 'test_request_type'
    ], false);

    $message = new Message($leaveRequest, $this->leaveRequestTemplateFactory, $this->getManagerService());
    $this->assertNull($message->getTemplateParameters($leaveRequest->contact_id));
  }

  public function testGetTemplateIDReturnsNullWhenThereIsNoTemplateForARequestType() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' =>$this->leaveContact['id'],
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
      'request_type' => 'test_request_type'
    ], false);

    $message = new Message($leaveRequest, $this->leaveRequestTemplateFactory, $this->getManagerService());
    $this->assertNull($message->getTemplateID());
  }

  private function getManagerService() {
    $managerService = $this->prophesize(LeaveManagerService::class);

    return $managerService->reveal();
  }
}
