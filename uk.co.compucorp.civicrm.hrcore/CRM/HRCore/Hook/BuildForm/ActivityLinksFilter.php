<?php

class CRM_HRCore_Hook_BuildForm_ActivityLinksFilter {

  /**
   * Determines what happens if the hook is handled.
   * Basically, it filters the activity type links on
   * the activities tab.
   * 
   * @param string $formName
   * @param object $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName)) {
      return;
    }

    $this->filterActivityTypeLinks($form);
  }

  /**
   * Checks if the hook should be handled.
   * 
   * @param string $formName
   * 
   * @return bool
   */
  private function shouldHandle($formName) {
    if($formName == 'CRM_Activity_Form_ActivityLinks') {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Filters the activity type links on the activities tab on the
   * contact summary page so that only links related to the Email,
   * Inbound Email, Reminder Sent and Print PDf Letter activity types
   * are returned.
   *
   * @param object $form
   */
  private function filterActivityTypeLinks($form) {
    $allowedActivities = $this->getAllowedActivityTypes();
    $activityTypes = [];
    $activityTypeLinks = $form->get_template_vars('activityTypes');

    foreach($activityTypeLinks as $id => $activityTypeLink) {
      if(in_array($activityTypeLink['name'], $allowedActivities))  {
        $activityTypes[$id] = $activityTypeLink;
      }
    }

    $form->assign('activityTypes', $activityTypes);
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
