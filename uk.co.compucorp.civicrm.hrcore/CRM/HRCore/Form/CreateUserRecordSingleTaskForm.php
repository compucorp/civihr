<?php

use CRM_HRCore_Form_CreateUserRecordTaskForm as CreateUserRecordTaskForm;

class CRM_HRCore_Form_CreateUserRecordSingleTaskForm extends CreateUserRecordTaskForm {

  /**
   * @inheritdoc
   */
  public function buildQuickForm() {
    parent::buildQuickForm();
    CRM_Utils_System::setTitle(ts('Create User Account'));
  }

  /**
   * @inheritdoc
   */
  public function preProcess() {
    $cid = CRM_Utils_Request::retrieve('cid', 'Integer');

    // set in session to use in post processing
    if ($cid) {
      $this->set('_contactIds', [$cid]);
    }

    $this->_contactIds = $this->get('_contactIds');
    $this->initContactDetails();

    if (empty($this->contactDetails)) {
      throw new \Exception('No contact selected');
    }

    $this->assign('invalidEmailContacts', $this->getContactsWithInvalidEmail());
    $this->assign('contactsWithAccount', $this->getContactsWithAccount());
    $this->assign('contactsForCreation', $this->getValidContactsForCreation());
    $this->assign('emailConflictContact', $this->getEmailConflictContacts());
  }
}
