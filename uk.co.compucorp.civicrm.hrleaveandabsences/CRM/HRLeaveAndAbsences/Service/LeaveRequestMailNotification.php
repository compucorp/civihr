<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachment as LeaveRequestAttachmentService;
use CRM_HRLeaveAndAbsences_BAO_NotificationReceiver as NotificationReceiver;
use CRM_Core_BAO_MessageTemplate as MessageTemplate;
use CRM_Core_BAO_Email as Email;

class CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotification {

  use CRM_HRLeaveAndAbsences_Service_SettingsManagerTrait;

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveRequestComment
   */
  private $leaveRequestComment;

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachment
   */
  private $leaveRequestAttachment;

  /**
   * @var String
   *   The From email configured on the site for sending out emails.
   */
  private $fromEmail;

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest status_id field.
   */
  private $leaveStatuses;

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest sickness_required_documents field.
   */
  private $sicknessRequiredDocuments;

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest sickness_reason field.
   */
  private $sicknessReasons;

  /**
   * @var array|null
   *   Stores the list of option values for the LeaveRequest from_date_type field.
   */
  private $leaveRequestDayTypes;

  /**
   * CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotification constructor.
   *
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveRequestComment $leaveRequestComment
   * @param \CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachment $leaveRequestAttachment
   */
  public function __construct(
    LeaveRequestCommentService $leaveRequestComment,
    LeaveRequestAttachmentService $leaveRequestAttachment
  ) {
    $this->leaveRequestComment = $leaveRequestComment;
    $this->leaveRequestAttachment = $leaveRequestAttachment;
  }

  /**
   * Send Email using the CRM_Core_BAO_MessageTemplate::sendTemplate method.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   */
  public function send(LeaveRequest $leaveRequest) {
    $leaveTemplate = $this->getTemplate($leaveRequest);
    if(!in_array($leaveRequest->request_type, [
      LeaveRequest::REQUEST_TYPE_SICKNESS, LeaveRequest::REQUEST_TYPE_TOIL, LeaveRequest::REQUEST_TYPE_LEAVE])
      || !$leaveTemplate
    )
    {
      return false;
    }

    $recipientEmails = $this->getRecipientEmails($leaveRequest);
    $status = [];
    foreach($recipientEmails as $value) {

      list($mailSent, $subject, $message, $html) = CRM_Core_BAO_MessageTemplate::sendTemplate(
        [
          'messageTemplateID' => $leaveTemplate->id,
          'contactId' => $leaveRequest->contact_id,
          'tplParams' => $this->getTemplateParameters($leaveRequest),
          'leaveRequestLink' => $this->getLeaveRequestURL(),
          'from' => $this->getFromEmail(),
          'toName' => $value['api.Contact.get']['values'][0]['display_name'],
          'toEmail' => $value['email'],
        ]
      );

      $status[$value['email']] = $mailSent;
    }

    return $status;
  }

  /**
   * Returns the array of the option values for the LeaveRequest status_id field.
   *
   * @return array
   */
  private function getLeaveRequestStatuses() {
    if (is_null($this->leaveStatuses)) {
      $this->leaveStatuses = LeaveRequest::buildOptions('status_id');
    }

    return $this->leaveStatuses;
  }

  /**
   * Returns the array of the option values for the LeaveRequest sickness_reason field.
   *
   * @return array
   */
  private function getSicknessReasons() {
    if (is_null($this->sicknessReasons)) {
      $this->sicknessReasons = LeaveRequest::buildOptions('sickness_reason');
    }

    return $this->sicknessReasons;
  }

  /**
   * Returns the array of the option values for the LeaveRequest sickness_required_documents field.
   *
   * @return array
   */
  private function getSicknessRequiredDocuments() {
    if (is_null($this->sicknessRequiredDocuments)) {
      $result = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => 'hrleaveandabsences_leave_request_required_document',
      ]);

      $options = [];
      foreach ($result['values'] as $requiredDocument) {
        $options[$requiredDocument['value']] = $requiredDocument['label'];
      }

      $this->sicknessRequiredDocuments = $options;
    }

    return $this->sicknessRequiredDocuments;
  }

  /**
   * Returns the array of the option values for the LeaveRequest from_date_type field.
   *
   * @return array
   */
  private function getLeaveRequestDayTypes() {
    if (is_null($this->leaveRequestDayTypes)) {
      $this->leaveRequestDayTypes = LeaveRequest::buildOptions('from_date_type');
    }

    return $this->leaveRequestDayTypes;
  }

  /**
   * Gets the Comments associated with this LeaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   */
  private function getLeaveComments(LeaveRequest $leaveRequest) {
    $result = civicrm_api3('LeaveRequest', 'getComment', [
      'leave_request_id' => $leaveRequest->id,
      'api.Contact.get' => ['id' => '$value.contact_id', 'return' => ['display_name']]
    ]);

    array_walk($result['values'], function(&$item){
      $item['commenter'] = $item['api.Contact.get']['values'][0]['display_name'];
    });

    return $result['values'];
  }

  /**
   * Gets the Attachments associated with this LeaveRequest
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   */
  private function getAttachments(LeaveRequest $leaveRequest) {
    $result = civicrm_api3('LeaveRequest', 'getAttachments', [
      'leave_request_id' => $leaveRequest->id
    ]);

    return $result['values'];
  }

  /**
   * Return URL for the leave request on SSP
   *
   * @return string
   */
  private function getLeaveRequestURL() {
    return CRM_Utils_System::url('my-leave', [], true);
  }

  /**
   * Return parameters to be used in the Email smarty template
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   */
  public function getTemplateParameters(LeaveRequest $leaveRequest) {
    $templateParameters =  [
      'leaveComments' => $this->getLeaveComments($leaveRequest),
      'leaveFiles' => $this->getAttachments($leaveRequest),
      'leaveRequestStatuses' => $this->getLeaveRequestStatuses(),
      'fromDate' => $this->formatLeaveRequestDate($leaveRequest->from_date),
      'toDate' => $this->formatLeaveRequestDate($leaveRequest->to_date),
      'fromDateType' => $this->getLeaveRequestDayTypes()[$leaveRequest->from_date_type],
      'toDateType' => $this->getLeaveRequestDayTypes()[$leaveRequest->to_date_type],
      'leaveStatus' => $this->getLeaveRequestStatuses()[$leaveRequest->status_id],
      'leaveRequestLink' => $this->getLeaveRequestURL(),
      'leaveRequest' => $leaveRequest,
    ];

    if ($leaveRequest->request_type == LeaveRequest::REQUEST_TYPE_SICKNESS) {
      $templateParameters['sicknessReasons'] = $this->getSicknessReasons();

      if ($leaveRequest->sickness_required_documents) {
        $templateParameters['sicknessRequiredDocuments'] = $this->getSicknessRequiredDocuments();
        $templateParameters['leaveRequiredDocuments'] =  explode(',', $leaveRequest->sickness_required_documents);
      }
    }

    return $templateParameters;
  }

  /**
   * get Email template to be used based on request type
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return \CRM_Core_DAO_MessageTemplate
   */
  public function getTemplate(LeaveRequest $leaveRequest) {
    $templateName = '';

    switch ($leaveRequest->request_type) {
      case LeaveRequest::REQUEST_TYPE_SICKNESS:
        $templateName = 'CiviHR Sickness Record Notification';
        break;
      case LeaveRequest::REQUEST_TYPE_LEAVE:
        $templateName = 'CiviHR Leave Request Notification';
        break;
      case LeaveRequest::REQUEST_TYPE_TOIL:
        $templateName = 'CiviHR TOIL Request Notification';
        break;
    }

    $params = ['msg_title' => $templateName, 'is_default' => 1];
    $defaults = [];
    $template = MessageTemplate::retrieve($params, $defaults);

    return $template;
  }

  /**
   * Gets the From Email address configured on the site
   * Currently It gets the from email address and name of the contact linked to the
   * default domain in civicrm_domain table.
   *
   * This logic will be changed later to fetch from L&A general settings
   *
   * @return string
   */
  private function getFromEmail() {
    if (is_null($this->fromEmail)) {
      $domainValues = [];
      $domain = CRM_Core_BAO_Domain::getDomain();
      $tokens = [
        'domain' => ['name', 'email'],
      ];

      foreach ($tokens['domain'] as $token) {
        $domainValues[$token] = CRM_Utils_Token::getDomainTokenReplacement($token, $domain);
      }

      $this->fromEmail = $domainValues['name'] . ' <' . $domainValues['email'] . '>';
    }
    return $this->fromEmail;
  }

  /**
   * Returns an array of emails of eligible recipients for this leave Request Notification
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   */
  public function getRecipientEmails(LeaveRequest $leaveRequest) {
    $leaveApprovers = $this->getLeaveApprovers($leaveRequest);

    if (empty($leaveApprovers)) {
      $leaveApprovers = $this->getNotificationReceivers($leaveRequest);
    }

    $contactEmail = [$leaveRequest->contact_id];
    $allRecipients = array_merge($leaveApprovers, $contactEmail);

    $result = civicrm_api3('Email', 'get', [
      'contact_id' => ['IN' => $allRecipients],
      'is_primary' => 1,
      'api.Contact.get' => ['id' => '$value.contact_id', 'return' => ['display_name']],
      'return' => ['email','contact_id']
    ]);

    return $result['values'];
  }

  /**
   * Gets the contactID's of Leave approvers for the current leave request
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   *   An array of contact IDs of leave approvers for the current leave request
   */
  private function getLeaveApprovers(LeaveRequest $leaveRequest) {
    $leaveApproverRelationships = $this->getLeaveApproverRelationshipsTypes();

    if (!$leaveApproverRelationships) {
      return [];
    }

    $relationshipTable = CRM_Contact_BAO_Relationship::getTableName();
    $relationshipTypeTable = CRM_Contact_BAO_RelationshipType::getTableName();
    $today = date('Y-m-d');

    $query = "
      SELECT r.contact_id_b
      FROM {$relationshipTable} r
      LEFT JOIN {$relationshipTypeTable} rt ON rt.id = r.relationship_type_id
      WHERE r.is_active = 1 AND rt.is_active = 1 
      AND rt.id IN(" . implode(',', $leaveApproverRelationships) . ")
      AND r.contact_id_a = {$leaveRequest->contact_id}
      AND (r.start_date IS NULL OR r.start_date <= '$today') 
      AND (r.end_date IS NULL OR r.end_date >= '$today')
    ";

    $result = CRM_Core_DAO::executeQuery($query);
    $leaveApprovers = [];

    while($result->fetch()) {
      $leaveApprovers[] = $result->contact_id_b;
    }

    return $leaveApprovers;
  }

  /**
   * Gets the contactID's of notification receivers linked to the absence type
   * of the current leave request.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return array
   *   An array of ContactID's of the notification receivers.
   */
  private function getNotificationReceivers(LeaveRequest $leaveRequest) {
    return NotificationReceiver::getReceiversIDsForAbsenceType($leaveRequest->type_id);
  }

  /**
   * Format Leave Request date in 'Y-m-d' format
   *
   * @param string $date
   *
   * @return string
   */
  private function formatLeaveRequestDate($date) {
    $leaveDate = new DateTime($date);
    return $leaveDate->format('Y-m-d');
  }
}
