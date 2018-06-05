<?php

/**
 * Class CRM_HRCore_Hook_ValidateForm_AdminFormOptionsValidation
 */
class CRM_HRCore_Hook_ValidateForm_AdminFormOptionsValidation {

  /**
   * Determines what happens if the hook is handled. Basically the hook
   * is only handled for option values of the 'toil_amounts' and 'leave_days_amounts'
   * option group. It basically ensures that the value of the value field of the
   * option values for these option groups are validated before submission and
   * if there are errors the form will not be submitted.
   *
   * @param string $formName
   * @param array $fields
   * @param mixed $files
   * @param object $form
   * @param array $errors
   */
  public function handle($formName, &$fields, &$files, &$form, &$errors) {
    if (!$this->shouldHandle($formName, $form)) {
      return;
    }

    $this->validateFormValues($fields, $errors);
  }

  /**
   * Checks if the hook should be handled.
   *
   * @param string $formName
   * @param object $form
   *
   * @return bool
   */
  private function shouldHandle($formName, $form) {
    if ($formName === CRM_Admin_Form_Options::class && $form->elementExists('value')) {
      $optionGroupName = $form->getVar( '_gName' );
      $optionGroupsToCheck = ['hrleaveandabsences_toil_amounts', 'hrleaveandabsences_leave_days_amounts'];

      if (in_array($optionGroupName, $optionGroupsToCheck)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Validates the value field and ensures that the user is only
   * allowed to enter a decimal or number and if it is a decimal, it
   * must be correct to 2 decimal places.
   *
   * @param array $fields
   * @param $errors
   */
  private function validateFormValues($fields, &$errors) {
    $value = CRM_Utils_Array::value('value', $fields);

    if (!filter_var($value, FILTER_VALIDATE_FLOAT)) {
      $errors['value'] = ts( 'Value must be a whole number or decimal' );
    }

    if (strpos($value, '.') !== FALSE) {
      $numbersAfterDecimal = substr($value, strpos($value, '.') + 1);
      if (strlen($numbersAfterDecimal) != 2) {
        $errors['value'] = ts( 'Please enter the value as a number correct to 2 decimal places' );
      }
    }
  }
}
