<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;

class  CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissions extends CRM_HRLeaveAndAbsences_API_Handler_LeaveFieldPermissions {

  use CRM_HRLeaveAndAbsences_ACL_LeaveInformationTrait;

  /**
   * @var array
   *   Stores the contact Id's that the current user has access to.
   */
  protected $accessibleContacts = [];

  /**
   * @var array
   *   The original API request data.
   */
  protected $apiRequest;

  /**
   * @var string
   *   User role of the currently logged in user.
   */
  public $loggedInUserRole;

  /**
   * @var LeaveRequestRightsService
   */
  protected $leaveRequestRightsService;

  /**
   * {@inheritdoc}
   */
  protected function getDataRowIdentifierKey() {
    return 'contact_id';
  }

  /**
   * {@inheritdoc}
   */
  protected function getDataRowIdentifierLevel() {
    return 1;
  }

  /**
   * @var array
   */
  private $restrictedFields = [
    'from_date_amount' => [
      'restricted_value' => '',
      'level' => 1
    ],
    'to_date_amount' => [
      'restricted_value' => '',
      'level' => 1
    ],
    'type_id' => [
      'restricted_value' => '',
      'level' => 1
    ],
    'balance_change' => [
      'restricted_value' => '',
      'level' => 1
    ],
    'toil_duration' => [
      'restricted_value' => '',
      'level' => 1
    ],
    'toil_to_accrue' => [
      'restricted_value' => '',
      'level' => 1
    ],
    'toil_expiry_date' => [
      'restricted_value' => '',
      'level' => 1
    ],
    'sickness_reason' => [
      'restricted_value' => '',
      'level' => 1
    ],
  ];

  /**
   * CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissions constructor.
   *
   * @param array $apiRequest
   *   The Original API request parameters.
   * @param LeaveRequestRightsService $leaveRequestRights
   */
  public function __construct($apiRequest, LeaveRequestRightsService $leaveRequestRights) {
    $this->leaveRequestRightsService = $leaveRequestRights;
    $this->apiRequest = $apiRequest;
    $this->setShouldRemoveDataRowIdentifier();
  }

  /**
   * {@inheritdoc}
   */
  protected function getRestrictedFields() {
    return $this->restrictedFields;
  }

  /**
   * Returns accessible contacts for a logged in user. i.e contact
   * that a user has access to via the Leave Request ACL rules.
   *
   * @return array
   */
  protected function getAccessibleContacts() {
    if(!$this->accessibleContacts && !$this->currentUserIsAdmin()) {
      $this->accessibleContacts = $this->leaveRequestRightsService->getLeaveContactsCurrentUserHasAccessTo();
    }

    return $this->accessibleContacts;
  }

  /**
   * {@inheritdoc}
   */
  protected function getNewFieldValue($field, $oldValue, $dataRowIdentifierValue) {
    $canAccessRestrictedData = $this->canAccessRestrictedData($dataRowIdentifierValue);

    if($this->currentUserIsAdmin() || $canAccessRestrictedData) {
      return $oldValue;
    }

    return $this->getRestrictedFields()[$field]['restricted_value'];
  }

  /**
   * Checks whether current user has access to the restricted data for the contact having its
   * data represented by the Identifier value.
   *
   * @param mixed $dataRowIdentifierValue
   *
   * @return bool
   */
  protected function canAccessRestrictedData($dataRowIdentifierValue) {
    return in_array($dataRowIdentifierValue, $this->getAccessibleContacts());
  }

  /**
   * Returns whether the current user is an Admin or not.
   *
   * @return bool
   */
  protected function currentUserIsAdmin() {
    return CRM_Core_Permission::check('administer leave and absences');
  }

  /**
   * Sets the value of the removeDataRowIdentifier variable.
   */
  private function setShouldRemoveDataRowIdentifier() {
    if(empty($this->apiRequest['params']['initial_return'])){
      return;
    }

    if (!in_array($this->getDataRowIdentifierKey(), $this->apiRequest['params']['initial_return'])) {
      $this->removeDataRowIdentifier = TRUE;
    }
  }
}
