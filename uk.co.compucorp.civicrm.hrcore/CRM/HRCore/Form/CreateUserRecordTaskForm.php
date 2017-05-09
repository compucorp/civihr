<?php

class CRM_HRCore_Form_CreateUserRecordTaskForm extends CRM_Contact_Form_Task {
  /**
   * @var array
   */
  protected $contactMapping = [];

  /**
   * @var string
   */
  private $checkboxName = 'send_email';

  /**
   * Build the form object.
   */
  public function buildQuickForm() {
    CRM_Utils_System::setTitle(ts('Create User Records'));
    foreach ($this->_contactIds as $key => $contactId) {
      $this->contactMapping[$key] = $contactId;
      $this->add('advcheckbox', sprintf('%s[%d]', $this->checkboxName, $key));
    }
    $this->addDefaultButtons(ts('Create Records'));
  }

  /**
   * Process the form after the input has been submitted and validated.
   */
  public function postProcess() {
    $values = $this->controller->exportValues($this->_name);
    $sendEmailValues = CRM_Utils_Array::value($this->checkboxName, $values, []);
    $sendEmailValues = array_map('boolval', $sendEmailValues);

    foreach ($sendEmailValues as $contactKey => $shouldSendEmail) {
      $contactID = CRM_Utils_Array::value($contactKey, $this->contactMapping);
      // todo send the emails blah blah
    }
  }

}
