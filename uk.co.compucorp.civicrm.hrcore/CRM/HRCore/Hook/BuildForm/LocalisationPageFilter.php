<?php

class CRM_HRCore_Hook_BuildForm_LocalisationPageFilter {
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
    if ($formName == CRM_Admin_Form_Setting_Localization::class) {
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
    $settings = [
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
    
    if (! $canViewAllFields) {
      $this->hideOptionLabels();
      foreach ($settings as $setting) {
        if ($form->elementExists($setting)) {
          $form->freeze($setting);
        }
      }
    }
  }
  
  /**
   * Hide labels for frozen elements
   */
  private function hideOptionLabels() {
    CRM_Core_Resources::singleton()->addStyle(
      'tr.crm-localization-form-block-lcMessages,
      tr.crm-localization-form-block-inheritLocale,
      tr.crm-localization-form-contact_default_language,
      tr.crm-localization-form-block-monetaryThousandSeparator,
      tr.crm-localization-form-block-monetaryDecimalPoint,
      tr.crm-localization-form-block-moneyformat,
      tr.crm-localization-form-block-moneyvalueformat,
      tr.crm-localization-form-block-customTranslateFunction,
      tr.crm-localization-form-block-legacyEncoding,
      tr.crm-localization-form-block-fieldSeparator { display: none; }'
    );
  }
  
}
