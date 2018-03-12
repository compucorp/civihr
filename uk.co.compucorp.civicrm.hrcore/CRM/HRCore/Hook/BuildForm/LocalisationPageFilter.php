<?php

class CRM_HRCore_Hook_BuildForm_LocalisationPageFilter {

  private $restrictedSettings = [
    'customTranslateFunction',
    'contact_default_language',
    'fieldSeparator',
    'inheritLocale',
    'lcMessages',
    'legacyEncoding',
    'monetaryThousandSeparator',
    'monetaryDecimalPoint',
    'moneyformat',
    'moneyvalueformat'
  ];
  /**
   * Hides options on the localisation page
   *
   * @param string $formName
   * @param CRM_Core_Form $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName)) {
      return;
    }
    
    $this->filterLocalisationOptions($form);
  }
  
  /**
   * Checks if the hook should be handled.
   *
   * @param string $formName
   *
   * @return bool
   */
  private function shouldHandle($formName) {
    if ($formName === CRM_Admin_Form_Setting_Localization::class) {
      return TRUE;
    }
    
    return FALSE;
  }
  
  /**
   * Hide options on localisation page if user does not have required permission
   *
   * @param CRM_Core_Form $form
   */
  private function filterLocalisationOptions($form) {
    $canViewAllFields = CRM_Core_Permission::check('access root menu items and configurations ');
    
    if (! $canViewAllFields) {
      $hiddenOptionsStyle = "tr.crm-localization-form-contact_default_language";
      
      foreach ($this->restrictedSettings as $setting) {
        if ($form->elementExists($setting)) {
          $hiddenOptionsStyle .= ", tr.crm-localization-form-block-$setting";
          $form->freeze($setting);
        }
      }
  
      $hiddenOptionsStyle .= ' { display: none; }';
      CRM_Core_Resources::singleton()->addStyle($hiddenOptionsStyle);
    }
  }
  
}
