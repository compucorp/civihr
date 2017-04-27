<?php

use CRM_HRLeaveAndAbsences_Mail_Template_TOILRequestNotificationTemplate as TOILRequestNotificationTemplate;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;
use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;


/**
 * Class CRM_HRLeaveAndAbsences_Mail_TOILRequestNotificationTemplateTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Mail_Template_TOILRequestNotificationTemplateTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveRequestHelpersTrait;
  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_MailHelpersTrait;


  private $toilRequestNotificationTemplate;

  public function setUp() {
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
    $leaveRequestCommentService = new LeaveRequestCommentService();
    $this->toilRequestNotificationTemplate = new TOILRequestNotificationTemplate($leaveRequestCommentService);

    $this->leaveRequestStatuses = $this->getLeaveRequestStatuses();
    $this->leaveRequestDayTypes = $this->getLeaveRequestDayTypes();
  }

  public function testGetTemplateIDReturnsTheCorrectID() {
    $templateDetails = $this->getTemplateDetails(['msg_title' => 'CiviHR TOIL Request Notification']);
    $templateID = $this->toilRequestNotificationTemplate->getTemplateID();
    $this->assertEquals($templateID, $templateDetails['id']);
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
      'toil_to_accrue' => 2,
      'request_type' => LeaveRequest::REQUEST_TYPE_TOIL
    ], false);

    //create 2 attachments for  the TOILRequest
    $attachment1 = $this->createAttachmentForLeaveRequest([
      'entity_id' => $leaveRequest->id,
      'name' => 'LeaveRequestSampleFile1.txt'
    ]);

    $attachment2 = $this->createAttachmentForLeaveRequest([
      'entity_id' => $leaveRequest->id,
      'name' => 'LeaveRequestSampleFile2.txt'
    ]);

    //add two comments for the TOIL request
    $params = [
      'leave_request_id' => $leaveRequest->id,
      'text' => 'Random Commenter',
      'contact_id' => $leaveRequest->contact_id,
      'sequential' => 1
    ];

    $leaveRequestCommentService = new LeaveRequestCommentService();
    $leaveRequestCommentService->add($params);
    $leaveRequestCommentService->add(array_merge($params, ['text' => 'Sample text']));

    $tplParams = $this->toilRequestNotificationTemplate->getTemplateParameters($leaveRequest);

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

    //There are two attachments for the TOIL request
    $this->assertCount(2, $tplParams['leaveFiles']);
    foreach($tplParams['leaveFiles'] as $file) {
      $this->assertContains($file['name'], [
        'LeaveRequestSampleFile1.txt', 'LeaveRequestSampleFile2.txt'
      ]);
    }

    //there are two comments for the TOIL request
    $this->assertCount(2, $tplParams['leaveComments']);
    foreach($tplParams['leaveComments'] as $comment) {
      $this->assertContains($comment['text'], ['Random Commenter', 'Sample text']);
      $this->assertEquals($comment['leave_request_id'], $leaveRequest->id);
    }
  }
}
