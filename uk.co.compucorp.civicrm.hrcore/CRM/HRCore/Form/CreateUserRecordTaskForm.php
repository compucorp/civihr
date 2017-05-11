<?php

use CRM_Utils_Array as ArrayHelper;
use CRM_HRCore_Service_DrupalUserService as DrupalUserService;

class CRM_HRCore_Form_CreateUserRecordTaskForm extends CRM_Contact_Form_Task {

  /**
   * @var array
   */
  protected $contactDetails = [];

  /**
   * @var bool
   */
  protected $sendEmail = FALSE;

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
    $this->drupalUserService = Civi::container()->get('drupal_user_service');
    parent::__construct($state, $action, $method, $name);
  }

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Create User Records'));
    $this->addDefaultButtons(ts('Create Records'));
    $this->add('advcheckbox', 'sendEmail', ts('Send Email'));
  }

  /**
   * Fetch contact details and set some template variables
   */
  public function preProcess() {
    parent::preProcess();
    $this->initContactDetails();
    $haveNoAccount = $this->getContactsWithout('uf_id');
    $haveAccount = array_diff_key($this->contactDetails, $haveNoAccount);
    $this->assign('contactsWithoutEmail', $this->getContactsWithout('email'));
    $this->assign('contactsWithAccount', $haveAccount);
    $this->assign('contactsForCreation', $this->getValidContactsForCreation());
    $this->assign('emailConflictContact', $this->getEmailConflictContacts());
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    $this->sendEmail = (bool) $this->getElementValue('sendEmail');
    $contactsToCreate = $this->getValidContactsForCreation();

    foreach ($contactsToCreate as $contact) {
      $this->createAccount($contact);
    }

    CRM_Core_Session::setStatus(
      ts('%1 new accounts were created', [1 => count($contactsToCreate)]),
      ts('Updates Saved'),
      'success'
    );
  }

  /**
   * Returns contacts that have a work email and no drupal account
   *
   * @return array
   */
  private function getValidContactsForCreation() {
    $missingEmail = $this->getContactsWithout('email');
    $haveNoAccount = $this->getContactsWithout('uf_id');
    $emailConflict = $this->getEmailConflictContacts();

    return array_diff_key($haveNoAccount, $missingEmail, $emailConflict);
  }

  /**
   * Create a Drupal account for a contact
   *
   * @param array $contact
   *
   * @return object
   */
  private function createAccount($contact) {
    $id = $contact['id'];
    $email = $contact['email'];
    $roles = ['civihr_staff'];

    $user = $this->drupalUserService->createNew($id, $email, TRUE, $roles);

    if ($this->sendEmail) {
      $this->drupalUserService->sendActivationMail($id, $user);
    }

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
      $this->setContactDetail($contactID, 'email', $email);
      $this->setContactDetail($contactID, 'id', $contactID);
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

  /**
   * Returns contacts with work emails that are already in use and pairs of new
   * Contacts that have duplicate emails
   *
   * @return array
   */
  private function getEmailConflictContacts() {
    $newAccounts = $this->getContactsWithout('uf_id');
    $haveNoEmail = $this->getContactsWithout('email');
    $newContactsWithEmail = array_diff_key($newAccounts, $haveNoEmail);

    if (empty($newContactsWithEmail)) {
      return [];
    }

    $newEmails = array_column($newContactsWithEmail, 'email');
    $params = ['uf_name' => ['IN' => $newEmails], 'options' => ['limit' => 0]];
    $existing = civicrm_api3('UFMatch', 'get', $params);
    $existing = ArrayHelper::value('values', $existing, []);
    $existingEmails = array_column($existing, 'uf_name');

    $duplicateEmails = array_diff_assoc($newEmails, array_unique($newEmails));
    $badEmails = array_merge($existingEmails, $duplicateEmails);

    $badContacts = [];
    foreach ($newContactsWithEmail as $contactID => $contact) {
      if (in_array($contact['email'], $badEmails)) {
        $badContacts[$contactID] = $contact;
      }
    }

    return array_unique($badContacts);
  }

}
