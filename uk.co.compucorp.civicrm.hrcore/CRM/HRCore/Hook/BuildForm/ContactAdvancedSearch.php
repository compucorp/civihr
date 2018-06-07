<?php

class CRM_HRCore_Hook_BuildForm_ContactAdvancedSearch {
  /**
   * Checks if the form is the contact advance search and removes
   * some unnused fields if so.
   *
   * @param string $formName
   * @param CRM_Core_Form $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName)) {
      return;
    }

    $this->removeUnnusedSearchFields($form);
  }

  /**
   * Returns true if current form is for the contact advanced search.
   * This indicates if the class should modify the form.
   *
   * @param string $formName
   *
   * @return bool
   */
  private function shouldHandle($formName) {
    $expectedFormName = 'CRM_Contact_Form_Search_Advanced';

    return $formName === $expectedFormName;
  }

  /**
   * Removes a few unnused fields from the contact advanced search, basic
   * section.
   *
   * @param CRM_Core_Form $form
   */
  private function removeUnnusedSearchFields($form) {
    $basicSearchFields = $form->get_template_vars('basicSearchFields');
    $fieldsToRemove = [
      'privacy_toggle',
      'preferred_communication_method',
      'preferred_language'
    ];

    foreach ($fieldsToRemove as $fieldToRemove) {
      unset($basicSearchFields[$fieldToRemove]);
    }

    $form->assign('basicSearchFields', $basicSearchFields);
  }

}
