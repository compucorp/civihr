<?php
use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;
use CRM_HRCore_Hook_BuildForm_ContactAdvancedSearch as ContactAdvancedSearch;

class CRM_HRCore_Hook_BuildForm_ContactAdvancedSearchTest extends BaseHeadlessTest {

  /**
   * Assets that the hook removes the right fields and leaves others untouched.
   */
  public function testRemovingUnusedFields() {
    $form = new CRM_Core_Form();
    $formName = 'CRM_Contact_Form_Search_Advanced';
    $form->assign('basicSearchFields', [
      'sort_name' => [],
      'privacy_toggle' => [],
      'preferred_communication_method' => [],
      'preferred_language' => [],
    ]);

    $hook = new ContactAdvancedSearch();
    $hook->handle($formName, $form);
    $fields = $form->get_template_vars('basicSearchFields');

    $this->assertEquals($fields, [
      'sort_name' => []
    ]);
  }

  /**
   * Asserts that the hook is going to be executed only for the contact
   * advanced search form.
   */
  public function testNotRemovingFieldsIfNotContactAdvancedSearchForm() {
    $form = new CRM_Core_Form();
    $formName = 'CRM_RelationshipType_Form';
    $form->assign('basicSearchFields', $this->getBasicSearchFields());

    $hook = new ContactAdvancedSearch();
    $hook->handle($formName, $form);
    $fields = $form->get_template_vars('basicSearchFields');

    $this->assertEquals($fields, $this->getBasicSearchFields());
  }

  /**
   * Returns a mock list of basic search fields.
   *
   * @return array
   */
  private function getBasicSearchFields() {
    return [
      'sort_name' => [],
      'privacy_toggle' => [],
      'preferred_communication_method' => [],
      'preferred_language' => [],
    ];
  }

}
