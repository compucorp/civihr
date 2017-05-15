<?php

use CRM_HRCore_Form_AbstractDrupalInteractionTaskForm as AbstractDrupalInteractionTaskForm;

class CRM_HRCore_Form_SendInvitationEmailTaskForm extends AbstractDrupalInteractionTaskForm {

  /**
   * @var bool
   */
  protected $resend = FALSE;

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Send Invitation Email'));
    $this->addDefaultButtons(ts('Create Records'));
    $this->add('advcheckbox', 'resendEmail', ts('Resend?'));
  }

  /**
   * Fetch contact details and set some template variables
   */
  public function preProcess() {
    parent::preProcess();
    $this->assign('contactsForSending', $this->getContactsToSendMailTo());
    $this->assign('contactsWithoutEmail', $this->getContactsWithoutAttribute('email'));
    $this->assign('contactsWithoutAccount', $this->getContactsWithoutAttribute('uf_id'));
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    $this->resend = (bool) $this->getElementValue('resendEmail');
    // todo select contactToSendMailTo depending on onboarding status
    $toBeSent = $this->getContactsToSendMailTo();
    foreach ($toBeSent as $contact) {
      $id = $contact['id'];
      $email = $contact['email'];
      $this->drupalUserService->sendActivationMail($id, $email);
    }

    CRM_Core_Session::setStatus(
      ts('%1 invitation emails were sent', [1 => count($toBeSent)]),
      ts('Emails Sent'),
      'success'
    );
  }

  /**
   * Gets contacts the have an email set
   *
   * @return array
   */
  private function getContactsToSendMailTo() {
    $haveNoEmail = $this->getContactsWithoutAttribute('email');
    $haveNoAccount = $this->getContactsWithoutAttribute('uf_id');

    return array_diff_key($this->contactDetails, $haveNoEmail, $haveNoAccount);
  }

}
