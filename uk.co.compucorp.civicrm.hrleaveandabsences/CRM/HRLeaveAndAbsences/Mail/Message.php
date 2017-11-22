<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_NotificationReceiver as NotificationReceiver;
use CRM_HRLeaveAndAbsences_Mail_Template_BaseRequestNotification as BaseRequestNotificationTemplate;
use CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate as RequestNotificationTemplateFactory;

class CRM_HRLeaveAndAbsences_Mail_Message {

  use CRM_HRLeaveAndAbsences_Service_SettingsManagerTrait;

  /**
   * @var array
   */
  private $templateParameters;

  /**
   * @var \CRM_HRLeaveAndAbsences_BAO_LeaveRequest
   */
  private $leaveRequest;

  /**
   * @var \CRM_HRLeaveAndAbsences_Mail_Template_BaseRequestNotification
   */
  private $requestTemplate;

  /**
   * @var \CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate
   */
  private $requestTemplateFactory;

  /**
   * CRM_HRLeaveAndAbsences_Mail_Message constructor.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   * @param \CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate $requestTemplateFactory
   */
  public function __construct(
    LeaveRequest $leaveRequest,
    RequestNotificationTemplateFactory $requestTemplateFactory
  ) {
    $this->leaveRequest = $leaveRequest;
    $this->requestTemplateFactory = $requestTemplateFactory;
  }

  /**
   * Gets the default From Email address configured on the site
   * at civicrm/admin/options/from_email_address?reset=1.
   * from the from_email_address civi option group.
   *
   * If there is no default from email address, the first from
   * email address is returned.
   *
   * @return string|null
   */
  public function getFromEmail() {
    $fromEmail = $this->getDefaultFromEmailAddress();

    if($fromEmail) {
      return $fromEmail;
    }

    return $this->getFirstFromEmailAddress();
  }

  /**
   * Gets the Request Template for the Leave Request type
   *
   * @return \CRM_HRLeaveAndAbsences_Mail_Template_BaseRequestNotification
   */
  private function getTemplate() {
    if (is_null($this->requestTemplate)) {
      $this->requestTemplate = $this->requestTemplateFactory->create($this->leaveRequest);
    }
    return $this->requestTemplate;
  }

  /**
   * Gets the template parameters for the Leave Request template
   * for the recipient.
   *
   * @param int $recipientID
   *
   * @return array|null
   */
  public function getTemplateParameters($recipientID) {
    if (!$this->isValidTemplate()) {
      return null;
    }

    if (is_null($this->templateParameters)) {
      $this->templateParameters = $this->getTemplate()->getTemplateParameters($this->leaveRequest);
    }

    $leaveRequestLink = ['leaveRequestLink' => $this->getLeaveRequestURL($recipientID)];

    return array_merge($this->templateParameters, $leaveRequestLink);
  }

  /**
   * Gets the template ID for the Leave Request template
   *
   * @return int|null
   */
  public function getTemplateID() {
    if (!$this->isValidTemplate()) {
      return null;
    }

    return $this->getTemplate()->getTemplateID();
  }

  /**
   * Gets the contact ID of the leave request
   *
   * @return int
   */
  public function getLeaveContactID() {
    return $this->leaveRequest->contact_id;
  }

  /**
   * Gets appropriate leave Request URL Link for the Contact
   *
   * @param int $contactID
   *  The contact to get the Leave Request URL for
   *
   * @return string
   */
  private function getLeaveRequestURL($contactID) {
    $queryString = '?leave-request-id=' . $this->leaveRequest->id;
    $leaveUrl = CRM_Utils_System::url('my-leave#/my-leave/report' .
      $queryString, [], true);

    if ($this->leaveRequest->contact_id != $contactID) {
      $leaveUrl = CRM_Utils_System::url('manager-leave#/manager-leave/requests' .
        $queryString, [], true);
    }

    return $leaveUrl;
  }

  /**
   * Returns an array of emails of eligible recipients for this leave Request Notification
   *
   * @return array
   */
  public function getRecipientEmails() {
    $leaveApprovers = $this->getLeaveApprovers();

    if (empty($leaveApprovers)) {
      $leaveApprovers = $this->getNotificationReceivers();
    }

    $leaveContact = [$this->leaveRequest->contact_id];
    $allRecipients = array_merge($leaveApprovers, $leaveContact);

    $result = civicrm_api3('Email', 'get', [
      'contact_id' => ['IN' => $allRecipients],
      'is_primary' => 1,
      'api.Contact.get' => ['id' => '$value.contact_id', 'return' => ['display_name']],
      'return' => ['email','contact_id']
    ]);

    return $result['values'];
  }

  /**
   * Gets the Contact ID's of the currently active Leave Approvers of the given Leave Request
   *
   * @return array
   *   An array of contact IDs of leave approvers for the current leave request
   */
  private function getLeaveApprovers() {
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
      AND r.contact_id_a = {$this->leaveRequest->contact_id}
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
   * @return array
   *   An array of ContactID's of the notification receivers.
   */
  private function getNotificationReceivers() {
    return NotificationReceiver::getReceiversIDsForAbsenceType($this->leaveRequest->type_id);
  }

  /**
   * Checks whether the template is a valid template or not.
   *
   * @return bool
   */
  private function isValidTemplate() {
    return $this->getTemplate() instanceof BaseRequestNotificationTemplate;
  }

  /**
   * Returns the first email address from the
   * from_email_address option group.
   *
   * @param array $params
   *
   * @return string|null
   */
  private function getFirstFromEmailAddress($params = []) {
    $params = array_merge([
      'option_group_id' => 'from_email_address',
      'options' => ['limit' => 1, 'sort' => 'weight ASC'],
      'return' => ['label']
    ], $params);

    $result = civicrm_api3('OptionValue', 'get', $params);

    $result = array_shift($result['values']);

    return $result ? $result['label'] : null;
  }

  /**
   * Returns the default from email address from the
   * from_email_address option group.
   *
   * @return string|null
   */
  private function getDefaultFromEmailAddress() {
    return $this->getFirstFromEmailAddress(['is_default' => 1]);
  }
}
