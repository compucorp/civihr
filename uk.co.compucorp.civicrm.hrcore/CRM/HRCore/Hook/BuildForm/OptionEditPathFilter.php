<?php

class CRM_HRCore_Hook_BuildForm_OptionEditPathFilter {

  /**
   * @var bool[]
   *   A mapping of option group name to its "is_locked" status
   */
  private $optionGroupLockedStatuses = [];

  /**
   * Handle the form. Any form that can contain elements referencing an
   * option group should be handled, i.e. all forms
   *
   * @param string $formName
   * @param CRM_Core_Form $form
   */
  public function handle($formName, &$form) {
    $this->filterOptionEditPaths($form);
  }

  /**
   * Loop through the form, removing the 'data-option-edit-path' from form
   * elements that reference locked option groups. If this attribute is not set
   * then the icon to provide a shortcut to edit this group will not be shown.
   *
   * @see \CRM_Core_Form_Renderer::addOptionsEditLink
   *
   * @param CRM_Core_Form $form
   */
  private function filterOptionEditPaths($form) {
    /** @var HTML_QuickForm_element $element */
    foreach ($form->_elements as &$element) {
      $optionEditPath = $element->getAttribute('data-option-edit-path');
      if (!$optionEditPath) {
        continue;
      }

      $prefix = 'civicrm/admin/options/';
      if (strpos($optionEditPath, $prefix) !== 0) {
        // this is not an option group edit link
        continue;
      }

      $optionGroupName = str_replace($prefix, '', $optionEditPath);

      if ($this->isLockedOptionGroup($optionGroupName)) {
        $element->removeAttribute('data-option-edit-path');
      }
    }
  }

  /**
   * Check whether a given option group is locked
   *
   * @param string $optionGroupName
   *
   * @return bool
   */
  private function isLockedOptionGroup($optionGroupName) {
    if (!isset($this->optionGroupLockedStatuses[$optionGroupName])) {
      $params = ['return' => 'is_locked', 'name' => $optionGroupName];
      $result = civicrm_api3('OptionGroup', 'get', $params);
      $result = array_shift($result['values']);
      $isLocked = CRM_Utils_Array::value('is_locked', $result, FALSE);
      $this->optionGroupLockedStatuses[$optionGroupName] = (bool) $isLocked;
    }

    return $this->optionGroupLockedStatuses[$optionGroupName];
  }

}
