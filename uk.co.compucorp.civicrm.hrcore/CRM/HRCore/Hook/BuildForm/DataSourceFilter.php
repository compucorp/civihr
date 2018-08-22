<?php

class CRM_HRCore_Hook_BuildForm_DataSourceFilter {

  /**
   * Handle the form. if the form is of type DataSource, it should be Handled
   *
   * @param string $formName
   * @param CRM_Core_Form $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName)) {
      return;
    }

    $this->setContactType($form);
  }

  /**
   * Sets the default selected contact type in the form, using the paramter
   * value
   *
   * @param CRM_Core_Form $form
   */
  private function setContactType(&$form) {
    $force = CRM_Utils_Request::retrieve('force', 'Boolean');
    $contactType = CRM_Utils_Request::retrieve('contactType', 'String');

    if (!$force) {
      return;
    }

    foreach ($form->_elements as $index => &$element) {
      if (isset($element->_name) && $element->_name === 'contactType') {
        foreach ($element->_elements as $radioInput) {
          if ($radioInput->_text === $contactType) {
            $radioInput->setChecked(TRUE);
            break;
          }
        }
      }
    }
  }

  /**
   * Checks if the hook should be handled.
   *
   * @param string $formName
   *
   * @return bool
   */
  private function shouldHandle($formName) {
    return $formName === CRM_Contact_Import_Form_DataSource::class;
  }

}
