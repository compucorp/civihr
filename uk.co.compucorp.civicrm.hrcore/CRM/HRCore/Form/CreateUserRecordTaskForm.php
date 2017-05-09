<?php

use CRM_Utils_Array as ArrayHelper;

class CRM_HRCore_Form_CreateUserRecordTaskForm extends CRM_Contact_Form_Task {

  /**
   * @var array
   */
  protected $contactDetails = [];

  /**
   * @var DrupalUserService
   */
  protected $drupalUserService;

  /**
   * @param null $state
   * @param mixed $action
   * @param string $method
   * @param null $name
   */
  public function __construct(
    $state = NULL,
    $action = CRM_Core_Action::NONE,
    $method = 'post',
    $name = NULL
  ) {
    // todo change to container get()
    $this->drupalUserService = new DrupalUserService();
    parent::__construct($state, $action, $method, $name);
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Create User Records'));
    $this->addDefaultButtons(ts('Create Records'));
  }

  /**
   * Fetch contact details and set some template variables
   */
  public function preProcess() {
    parent::preProcess();
    $this->initContactDetails();
    // todo assign variables for form, num missing email etc..
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    foreach ($this->getValidContactForCreation() as $contact) {
      $this->createAccount($contact['work_email']);
    }
  }

  /**
   * Returns contacts that have a work email and no drupal account
   *
   * @return array
   */
  private function getValidContactForCreation() {
    return array_diff(
      $this->contactDetails,
      $this->getContactsWithout('work_email'),
      $this->getContactsWithout('uf_id')
    );
  }

  /**
   * Create a Drupal account for a contact
   *
   * @param $email
   *
   * @return object
   */
  private function createAccount($email) {
    $user = $this->drupalUserService->createNew($email);
    $user->status = 1; // unblock user (will send invitation mail)
    $this->drupalUserService->addRoles($user, ['civihr_staff']);

    return $user;
  }

  /**
   * Creates an array to store contact details
   */
  private function initContactDetails() {
    $emailParams = [
      'contact_id' => '$value.id',
      'return' => ['email'],
      'location_type_id' => 'work'
    ];
    $ufMatchParams = [
      'contact_id' => '$value.id',
      'return' => ['uf_id']
    ];
    $params = [
      'return' => ['display_name'],
      'id' => ['IN' => $this->_contactIds],
      'options' => ['limit' => 0],
      'api.Email.getsingle' => $emailParams,
      'api.UFMatch.getsingle' => $ufMatchParams,
    ];
    $contactDetails = civicrm_api3('Contact', 'get', $params);
    $contactDetails = ArrayHelper::value('values', $contactDetails);

    foreach ($contactDetails as $detail) {
      $contactID = (int) ArrayHelper::value('contact_id', $detail);
      $displayName = ArrayHelper::value('display_name', $detail);
      $ufMatch = ArrayHelper::value('api.UFMatch.getsingle', $detail, []);
      $ufId = (int) ArrayHelper::value('uf_id', $ufMatch);
      $emailDetails = ArrayHelper::value('api.Email.getsingle', $detail, []);
      $email = ArrayHelper::value('email', $emailDetails);

      $this->setContactDetail($contactID, 'display_name', $displayName);
      $this->setContactDetail($contactID, 'uf_id', $ufId);
      $this->setContactDetail($contactID, 'work_email', $email);
    }
  }

  /**
   * Helper method to prevent overwriting of contact details
   *
   * @param int $contactId
   * @param string $type
   * @param mixed $value
   */
  private function setContactDetail($contactId, $type, $value) {
    if (!isset($this->contactDetails[$contactId][$type])) {
      $this->contactDetails[$contactId][$type] = $value;
    }
  }

  /**
   * @param $property
   *  The property to check if empty
   *
   * @return array
   */
  protected function getContactsWithout($property) {
    $checker = function ($contactDetail) use ($property) {
      return empty($contactDetail[$property]);
    };

    return array_filter($this->contactDetails, $checker);
  }

}
