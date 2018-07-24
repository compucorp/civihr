<?php

trait CRM_HRCore_Upgrader_Steps_1021 {

  /**
   * This task adds the default assignee option values that can be selected when
   * creating or editing a new workflow's activity.
   *
   * @return bool
   */
  public function upgrade_1021() {
    // Add option group for activity default assignees:
    CRM_Core_BAO_OptionGroup::ensureOptionGroupExists([
      'name' => 'activity_default_assignee',
      'title' => ts('Activity default assignee'),
      'is_reserved' => 1,
    ]);

    // Add option values for activity default assignees:
    $options = [
      ['name' => 'NONE', 'label' => ts('None'), 'is_default' => 1],
      ['name' => 'BY_RELATIONSHIP', 'label' => ts('By relationship to case client')],
      ['name' => 'SPECIFIC_CONTACT', 'label' => ts('Specific contact')],
      ['name' => 'USER_CREATING_THE_CASE', 'label' => ts('User creating the case')],
    ];

    foreach ($options as $option) {
      CRM_Core_BAO_OptionValue::ensureOptionValueExists([
        'option_group_id' => 'activity_default_assignee',
        'name' => $option['name'],
        'label' => $option['label'],
        'is_default' => CRM_Utils_Array::value('is_default', $option, 0),
        'is_active' => TRUE,
      ]);
    }

    return TRUE;
  }

}
