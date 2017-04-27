<?php

use CRM_HRLeaveAndAbsences_Mail_Template_LeaveRequestNotificationTemplate as LeaveRequestNotificationTemplate;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;


/**
 * Class CRM_HRLeaveAndAbsences_Mail_LeaveRequestNotificationTemplateTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Mail_Template_LeaveRequestNotificationTemplateTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_MailHelpersTrait;


  private $leaveRequestNotificationTemplate;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $leaveRequestCommentService = new LeaveRequestCommentService();
    $this->leaveRequestNotificationTemplate = new LeaveRequestNotificationTemplate($leaveRequestCommentService);

    $this->leaveRequestStatuses = $this->getLeaveRequestStatuses();
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
  }

  public function testGetTemplateIDReturnsTheCorrectID() {
    $templateDetails = $this->getTemplateDetails(['msg_title' => 'CiviHR Leave Request Notification']);
    $templateID = $this->leaveRequestNotificationTemplate->getTemplateID();
    $this->assertEquals($templateID, $templateDetails['id']);
  }

  public function testGetTemplateParametersReturnsTheExpectedParametersForTheTemplate() {
    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'type_id' => 1,
      'contact_id' =>2,
      'from_date' => CRM_Utils_Date::processDate('tomorrow'),
      'to_date' => CRM_Utils_Date::processDate('tomorrow'),
    ], false);

    //create 2 attachments for leaveRequest
    $attachment1 = $this->createAttachmentForLeaveRequest([
      'entity_id' => $leaveRequest->id,
      'name' => 'LeaveRequestSampleFile1.txt'
    ]);

    $attachment2 = $this->createAttachmentForLeaveRequest([
      'entity_id' => $leaveRequest->id,
      'name' => 'LeaveRequestSampleFile2.txt'
    ]);

    //add two comments for the leave request
    $params = [
      'leave_request_id' => $leaveRequest->id,
      'text' => 'Random Commenter',
      'contact_id' => $leaveRequest->contact_id,
      'sequential' => 1
    ];

    $this->createCommentForLeaveRequest($params);
    $this->createCommentForLeaveRequest(array_merge($params, ['text' => 'Sample text']));

    $tplParams = $this->leaveRequestNotificationTemplate->getTemplateParameters($leaveRequest);

    $leaveRequestDayTypes = LeaveRequest::buildOptions('from_date_type');
    $leaveRequestStatuses = LeaveRequest::buildOptions('status_id');
    $fromDate = new DateTime($leaveRequest->from_date);
    $toDate = new DateTime($leaveRequest->to_date);

    //validate template parameters
    $this->assertEquals($tplParams['toDate'], $toDate->format('Y-m-d'));
    $this->assertEquals($tplParams['fromDate'], $fromDate->format('Y-m-d'));
    $this->assertEquals($tplParams['leaveRequest'], $leaveRequest);
    $this->assertEquals($tplParams['fromDateType'], $leaveRequestDayTypes[$leaveRequest->from_date_type]);
    $this->assertEquals($tplParams['toDateType'], $leaveRequestDayTypes[$leaveRequest->to_date_type]);
    $this->assertEquals($tplParams['leaveStatus'], $leaveRequestStatuses[$leaveRequest->status_id]);
    $this->assertEquals($tplParams['leaveRequestLink'], CRM_Utils_System::url('my-leave', [], true));

    //There are two attachments for the leave request
    $this->assertCount(2, $tplParams['leaveFiles']);
    $leaveFileNames = array_column($tplParams['leaveFiles'], 'name');
    sort($leaveFileNames);

    $this->assertEquals($leaveFileNames, ['LeaveRequestSampleFile1.txt', 'LeaveRequestSampleFile2.txt']);

    //there are two comments for the leave request
    $this->assertCount(2, $tplParams['leaveComments']);
    $leaveCommentText = array_column($tplParams['leaveComments'], 'text');
    sort($leaveCommentText);

    $this->assertEquals($leaveCommentText, ['Random Commenter', 'Sample text']);
  }
}
