<?php

class CRM_HRCore_Hook_BuildForm_CustomImportFieldsFilter {
  /**
   * @var array
   *  An array of restricted fields as keys and
   *  the default values and css class for the field
   *  as array value.
   */
  private $restrictedFields = [
    'contactType' => [
      'defaultValue' => CRM_Import_Parser::CONTACT_INDIVIDUAL,
      'class' => '.crm-custom-import-uploadfile-from-block-contactType'
    ],
    'fieldSeparator' => [
      'defaultValue' => ',',
      'class' => '.crm-import-datasource-form-block-fieldSeparator'
    ],
  ];

  /**
   * Checks if the form is the custom field import form and
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
    $hiddenOptionsStyle = '';
    $defaults = $form->_defaultValues;

    foreach ($this->restrictedFields as $field => $properties) {
      if ($form->elementExists($field)) {
        $hiddenOptionsStyle .= $properties['class'] . ', ';
        $form->freeze($field);
        $defaults[$field] = $properties['defaultValue'];
      }
    }

    $hiddenOptionsStyle .= rtrim($hiddenOptionsStyle, ', ') . ' { display: none; }';
    CRM_Core_Resources::singleton()->addStyle($hiddenOptionsStyle);
    $form->setDefaults($defaults);
  }

  /**
   * Returns true if current form is for custom field import.
   * This indicates if the class should modify the form.
   *
   * @param string $formName
   *
   * @return bool
   */
  private function shouldHandle($formName) {
    return $formName === CRM_Custom_Import_Form_DataSource::class;
  }
}
