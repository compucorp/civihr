<?php

class CRM_HRCore_Hook_BuildForm_ActivityFilterSelectFieldsModifier {

  /**
   * Determines what happens if the hook is handled.
   * Basically, it modifies the include and exclude
   * activity type fields.
   *
   * @param string $formName
   * @param object $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName)) {
      return;
    }

    $this->modifyExcludeAndIncludeActivityTypeFields($form);
  }

  /**
   * Checks if the hook should be handled.
   *
   * @param string $formName
   *
   * @return bool
   */
  private function shouldHandle($formName) {
    if($formName == 'CRM_Activity_Form_ActivityFilter') {
      return TRUE;
    }

    return FALSE;
  }
  
  /**
   * Overrides the include and exclude select activity Form fields on the
   * activities tab of the contact summary page to display only activities of
   * type Email, Inbound Email, Reminder Sent, Print PDf Letter.
   *
   * @param object $form
   */
  private function modifyExcludeAndIncludeActivityTypeFields($form) {
    $allowedActivityTypes = $this->getAllowedActivityTypes();

    $activityTypes = civicrm_api3('OptionValue', 'get', [
      'option_group_id' => 'activity_type',
      'name' => ['IN' => $allowedActivityTypes],
    ]);

    $formActivityTypes = [];
    foreach ($activityTypes['values'] as $activityType) {
      $formActivityTypes[$activityType['value']] = $activityType['label'];
    }

    $form->add('select', 'activity_type_filter_id', ts('Include'), $formActivityTypes);
    $form->add('select',
      'activity_type_exclude_filter_id',
      ts('Exclude'),
      ['' => ts('- select activity type -')] + $formActivityTypes
    );
  }

  /**
   * Returns the allowed activity types.
   *
   * @return array
   */
  private function getAllowedActivityTypes() {
    return ['Email',  'Inbound Email', 'Reminder Sent', 'Print PDF Letter'];
  }
}
