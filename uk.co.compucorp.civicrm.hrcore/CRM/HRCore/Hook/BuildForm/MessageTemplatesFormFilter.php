<?php

class CRM_HRCore_Hook_BuildForm_MessageTemplatesFormFilter {
  
  /**
   * Restrict access to form page based on permission
   *
   * @param $formName
   * @param $form
   */
  public function handle($formName, &$form) {
    if (!$this->shouldHandle($formName)) {
      return;
    }
    
    $this->filterMessageTemplates($form);
  }
  
  /**
   * Checks if the hook should be handled.
   *
   * @param string $formName
   *
   * @return bool
   */
  private function shouldHandle($formName) {
    if ($formName === CRM_Admin_Form_MessageTemplates::class) {
      return TRUE;
    }
    
    return FALSE;
  }
  
  /**
   * Restrict access of message template editing based on permission
   *
   * @param CRM_Core_Form $form
   */
  private function filterMessageTemplates($form) {
    // Only system workflow message template have workflow id set
    $workflowId = $form->getVar("_workflow_id");
    if (isset($workflowId)) {
      $canView = CRM_Core_Permission::check('edit system workflow message templates');
    } else {
      $canView = CRM_Core_Permission::check('edit user-driven message templates');
    }
    
    if (! $canView && ! CRM_Core_Permission::check('edit message templates')) {
      CRM_Core_Session::setStatus(ts('You do not have permission to view requested page.'), ts('Access Denied'));
      $url = CRM_Utils_System::url('civicrm/admin/messageTemplates', "reset=1");
      CRM_Utils_System::redirect($url);
    }
  }
}
