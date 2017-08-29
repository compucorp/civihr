<?php

use CRM_Utils_Array as ArrayHelper;
use CRM_HRCore_Form_AbstractDrupalInteractionTaskForm as AbstractDrupalInteractionTaskForm;

class CRM_HRCore_Form_CreateUserRecordTaskForm extends AbstractDrupalInteractionTaskForm {

  /**
   * @inheritdoc
   */
  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Create User Account(s)'));
    $this->addDefaultButtons(ts('Create'));
    $this->add('advcheckbox', 'sendEmail', ts('Send welcome email?'));

    foreach ($this->getAssignableRoles() as $role) {
      $this->add('advcheckbox', sprintf('roles[%s]', $role), $role);
    }

    $this->addFormRule([$this, 'validateInput']);
  }

  /**
   * Fetch contact details and set some template variables
   */
  public function preProcess() {
    parent::preProcess();

    $haveAccount = $this->getContactsWithAccount();

    $this->assign('invalidEmailContacts', $this->getContactsWithInvalidEmail());
    $this->assign('contactsWithAccount', $haveAccount);
    $this->assign('contactsForCreation', $this->getValidContactsForCreation());
    $this->assign('emailConflictContact', $this->getEmailConflictContacts());
  }

  /**
   * @inheritdoc
   */
  public function postProcess() {
    $sendEmail = (bool) $this->getElementValue('sendEmail');
    $roles = array_keys(array_filter($this->getSubmitValue('roles')));
    $roles = array_intersect($roles, $this->getAssignableRoles());

    $contactsToCreate = $this->getValidContactsForCreation();

    foreach ($contactsToCreate as $contact) {
      $this->createAccount($contact, $roles);
    }

    if ($sendEmail) {
      $haveAccount = $this->getContactsWithAccount();
      $emailContacts = array_merge($contactsToCreate, $haveAccount);

      foreach($emailContacts as $contact) {
        $this->drupalUserService->sendActivationMail($contact['email']);
      }
    }

    $statusMsg = '%1 new accounts were created. %2 welcome emails were sent.';
    $msgVars = [
      1 => count($contactsToCreate),
      2 => empty($emailContacts) ? 'No' : count($emailContacts)
    ];

    CRM_Core_Session::setStatus(
      ts($statusMsg, $msgVars),
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
    $missingEmail = $this->getContactsWithInvalidEmail();
    $haveNoAccount = $this->getContactsWithoutAttribute('uf_id');
    $emailConflict = $this->getEmailConflictContacts();

    return array_diff_key($haveNoAccount, $missingEmail, $emailConflict);
  }

  /**
   * Create a Drupal account for a contact
   *
   * @param array $contact
   *   The contact to create an account for
   * @param array $roles
   *   The roles to be added to the new user
   *
   * @return object
   */
  private function createAccount($contact, $roles = []) {
    $email = $contact['email'];
    $user = $this->drupalUserService->createNew($email, TRUE, $roles);

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
    $params = ['uf_name' => ['IN' => $newEmails]];
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

  /**
   * @return array
   */
  private function getContactsWithInvalidEmail() {
    $invalid = [];

    foreach ($this->contactDetails as $contactID => $contact) {
      $email = $contact['email'];
      if (!$this->drupalUserService->isValidEmail($email)
        || !$this->drupalUserService->isValidUsername($email)
      ) {
        $invalid[$contactID] = $contact;
      }
    }

    return $invalid;
  }

  /**
   * @return array
   */
  private function getContactsWithAccount() {
    $haveNoAccount = $this->getContactsWithoutAttribute('uf_id');
    $haveAccount = array_diff_key($this->contactDetails, $haveNoAccount);

    return $haveAccount;
  }

  /**
   * @return array
   */
  private function getAssignableRoles() {
    $roles = ['civihr_admin', 'civihr_manager', 'civihr_staff'];
    $assignable = [];

    foreach ($roles as $role) {
      if ($this->canAssignRole($role)) {
        $assignable[] = $role;
      }
    }

    return $assignable;
  }

  /**
   * @param string $role
   *
   * @return bool
   */
  private function canAssignRole($role) {
    return CRM_Core_Permission::check(sprintf('assign %s role', $role));
  }

  /**
   * @param array $fields
   * @return array|true
   *   Array of errors if validation fails, true if everything is fine
   */
  public function validateInput($fields) {
    $errors = [];
    $roles = array_keys(array_filter(ArrayHelper::value('roles', $fields)));

    if (empty($roles)) {
      $errors['_qf_default'] = ts('You must select at least one role') . '<br/>';
    }

    $forbiddenRoles = array_diff($roles, $this->getAssignableRoles());
    if (!empty($forbiddenRoles)) {
      $err = ts('You selected roles you do not have permission to assign');
      $errors['_qf_default'] .= $err . '<br/>';
    }

    return empty($errors) ? TRUE : $errors;
  }

}
