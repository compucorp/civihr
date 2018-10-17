<?php

class CRM_HRCore_Hook_BuildForm_ContactImportFieldsFilter {

  /**
   * @var array
   *  A an array of restricted fields as keys and
   *  the default values they are to be set to
   *  as values.
   */
  private $restrictedFields = [
    'dataSource' => CRM_Import_DataSource_CSV::class,
    'contactType' => CRM_Import_Parser::CONTACT_INDIVIDUAL,
    'dedupe' => NULL,
    'fieldSeparator' => ',',
  ];

  /**
   * Checks if the form is the contact import form and
   * performs some logic.
   *
   * @param string $formName
   * @param CRM_Core_Form $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName)) {
      return;
    }

    $this->filterImportFields($form);
  }

  /**
   * Freezes the restricted fields and hides them from view and
   * also sets the default expected values for these fields to
   * avoid validation issues.
   *
   * @param CRM_Core_Form $form
   */
  private function filterImportFields($form) {
    $hiddenOptionsStyle = '#choose-data-source';
    $defaults = $form->_defaultValues;

    foreach ($this->restrictedFields as $field => $defaultValue) {
      if ($form->elementExists($field)) {
        $hiddenOptionsStyle .= ",  .crm-import-datasource-form-block-$field";
        $form->freeze($field);
        if ($defaultValue) {
          $defaults[$field] = $defaultValue;
        }
      }
    }

    $hiddenOptionsStyle .= ' { display: none; }';
    CRM_Core_Resources::singleton()->addStyle(
      $hiddenOptionsStyle,
      CRM_Core_Resources::DEFAULT_WEIGHT,
      'page-header'
    );
    $form->setDefaults($defaults);
  }

  /**
   * Returns true if current form is for contact import.
   * This indicates if the class should modify the form.
   *
   * @param string $formName
   *
   * @return bool
   */
  private function shouldHandle($formName) {
    return $formName === CRM_Contact_Import_Form_DataSource::class;
  }
}
