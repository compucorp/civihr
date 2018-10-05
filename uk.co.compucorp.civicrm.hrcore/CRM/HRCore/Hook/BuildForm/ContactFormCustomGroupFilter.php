<?php

class CRM_HRCore_Hook_BuildForm_ContactFormCustomGroupFilter {

  /**
   * Removes some custom groups from the accordion
   * list on the contact add/edit form page.
   * Presently, only the Contact_Length_Of_Service
   * custom group is filtered out.
   *
   * @param string $formName
   * @param CRM_Core_Form $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName)) {
      return;
    }

    $customGroup = civicrm_api3('CustomGroup', 'getsingle', [
      'name' => 'Contact_Length_Of_Service',
    ]);

    if (!$customGroup['id']) {
      return;
    }

    $customGroupTree = $form->get_template_vars('groupTree');
    //remove the Contact_Length_Of_Service from the custom group list.
    unset($customGroupTree[$customGroup['id']]);
    $form->assign('groupTree', $customGroupTree);
  }

  /**
   * Returns true if current form is for the contact add/edit form.
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
