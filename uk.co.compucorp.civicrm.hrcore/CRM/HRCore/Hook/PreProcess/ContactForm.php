<?php

class CRM_HRCore_Hook_PreProcess_ContactForm {

  /**
   * Checks if the form is the contact form and performs some
   * logic.
   *
   * @param string $formName
   * @param CRM_Core_Form $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName)) {
      return;
    }

    $this->setPageTitle($form);
  }

  /**
   * Sets the page title for the contact form when the contact type is
   * 'Individual'.
   *
   * @param CRM_Core_Form $form
   */
  private function setPageTitle($form) {
    $contactType = $form->getVar('_contactType');

    if ($contactType === 'Individual') {
      CRM_Utils_System::setTitle(ts('New Staff'));
    }
  }

  /**
   * Returns true if current form is for the contact form
   * This indicates if the class should modify the form.
   *
   * @param string $formName
   *
   * @return bool
   */
  private function shouldHandle($formName) {
    $expectedFormName = 'CRM_Contact_Form_Contact';

    return $formName === $expectedFormName;
  }
}
