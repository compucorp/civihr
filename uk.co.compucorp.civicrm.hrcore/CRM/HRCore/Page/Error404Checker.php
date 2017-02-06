<?php

require_once 'CRM/Core/Page.php';

/**
 * Handles CiviCRM paths that are not registered in menu table.
 */
class CRM_HRCore_Page_Error404Checker extends CRM_Core_Page {
  
  /**
   * Redirects to Tasks & Assignments Dashboard.
   */
  public function run() {
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/tasksassignments/dashboard', 'reset=1'));
  }
}
