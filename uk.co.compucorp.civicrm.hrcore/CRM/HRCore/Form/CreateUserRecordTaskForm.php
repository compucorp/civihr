<?php

use CRM_Utils_Array as ArrayHelper;
use CRM_HRCore_Form_AbstractDrupalInteractionTaskForm as AbstractDrupalInteractionTaskForm;

class CRM_HRCore_Form_CreateUserRecordTaskForm extends AbstractDrupalInteractionTaskForm {

  /**
   * @var bool
   */
  protected $sendEmail = FALSE;

  /**
   * @inheritdoc
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

    $haveNoAccount = $this->getContactsWithoutAttribute('uf_id');
    $haveAccount = array_diff_key($this->contactDetails, $haveNoAccount);

    $this->assign('contactsWithoutEmail', $this->getContactsWithoutAttribute('email'));
    $this->assign('contactsWithAccount', $haveAccount);
    $this->assign('contactsForCreation', $this->getValidContactsForCreation());
    $this->assign('emailConflictContact', $this->getEmailConflictContacts());
  }

  /**
   * @inheritdoc
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
   * Returns contacts that have an email and no drupal account
   *
   * @return array
   */
  private function getValidContactsForCreation() {
    $missingEmail = $this->getContactsWithoutAttribute('email');
    $haveNoAccount = $this->getContactsWithoutAttribute('uf_id');
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
      $this->drupalUserService->sendActivationMail($id, $email);
    }

    return $user;
  }

  /**
   * Returns contacts with emails that are already in use and pairs of new
   * Contacts that have duplicate emails
   *
   * @return array
   */
  private function getEmailConflictContacts() {
    $newAccounts = $this->getContactsWithoutAttribute('uf_id');
    $haveNoEmail = $this->getContactsWithoutAttribute('email');
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

    return $badContacts;
  }

}
