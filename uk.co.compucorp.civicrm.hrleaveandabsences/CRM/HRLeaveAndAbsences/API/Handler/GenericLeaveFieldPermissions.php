<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestRights as LeaveRequestRightsService;

class  CRM_HRLeaveAndAbsences_API_Handler_GenericLeaveFieldPermissions extends CRM_HRLeaveAndAbsences_API_Handler_LeaveFieldPermissions {

  use CRM_HRLeaveAndAbsences_ACL_LeaveInformationTrait;

  const ADMIN_ROLE = 'Admin';
  const MANAGER_ROLE = 'Manager';
  const STAFF_ROLE = 'Staff';

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
      'restricted_for' => [
        self::MANAGER_ROLE => ['replace_with' => ''],
        self::STAFF_ROLE => ['replace_with' => '']
      ],
      'level' => 1
    ],
    'to_date_amount' => [
      'restricted_for' => [
        self::MANAGER_ROLE => ['replace_with' => ''],
        self::STAFF_ROLE => ['replace_with' => '']
      ],
      'level' => 1
    ],
    'balance_change' => [
      'restricted_for' => [
        self::MANAGER_ROLE => ['replace_with' => ''],
        self::STAFF_ROLE => ['replace_with' => '']
      ],
      'level' => 1
    ],
    'toil_duration' => [
      'restricted_for' => [
        self::MANAGER_ROLE => ['replace_with' => ''],
        self::STAFF_ROLE => ['replace_with' => '']
      ],
      'level' => 1
    ],
    'toil_to_accrue' => [
      'restricted_for' => [
        self::MANAGER_ROLE => ['replace_with' => ''],
        self::STAFF_ROLE => ['replace_with' => '']
      ],
      'level' => 1
    ],
    'toil_expiry_date' => [
      'restricted_for' => [
        self::MANAGER_ROLE => ['replace_with' => ''],
        self::STAFF_ROLE => ['replace_with' => '']
      ],
      'level' => 1
    ],
    'sickness_reason' => [
      'restricted_for' => [
        self::MANAGER_ROLE => ['replace_with' => ''],
        self::STAFF_ROLE => ['replace_with' => '']
      ],
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
    $this->setLoggedInUserRole();
    $this->leaveRequestRightsService = $leaveRequestRights;
    $this->apiRequest = $apiRequest;
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
    $isAdminUser = $this->loggedInUserRole === self::ADMIN_ROLE;
    if(!$this->accessibleContacts && !$isAdminUser) {
      $this->accessibleContacts = $this->leaveRequestRightsService->getLeaveContactsCurrentUserHasAccessTo();
    }

    return $this->accessibleContacts;
  }

  /**
   * {@inheritdoc}
   */
  protected function getNewFieldValue($field, $oldValue, $dataRowIdentifierValue) {
    $isAdminUser = $this->loggedInUserRole === self::ADMIN_ROLE;
    $canAccessRestrictedData = $this->canAccessRestrictedData($dataRowIdentifierValue);

    if($isAdminUser || $canAccessRestrictedData) {
      return $oldValue;
    }

    $rolesRestrictedFor = array_keys($this->getRestrictedFields()[$field]['restricted_for']);
    $inRestrictedRoles = in_array($this->loggedInUserRole, $rolesRestrictedFor);

    if(!empty($inRestrictedRoles)) {
      return $this->getRestrictedFields()[$field]['restricted_for'][$this->loggedInUserRole]['replace_with'];
    }

    return $oldValue;
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
   * Sets the Role for the current logged in user.
   */
  protected function setLoggedInUserRole() {
    if (CRM_Core_Permission::check('administer leave and absences')) {
      $role = self::ADMIN_ROLE;
    }
    elseif(CRM_Core_Permission::check('manage leave and absences in ssp')) {
      $role = self::MANAGER_ROLE;
    }
    else {
      $role = self::STAFF_ROLE;
    }

    $this->loggedInUserRole = $role;
  }
}
