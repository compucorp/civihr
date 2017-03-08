<?php

/**
 * CRM_HRLeaveAndAbsences_Page_Dashboard
 */
class CRM_HRLeaveAndAbsences_Page_Dashboard extends CRM_Core_Page {
  public function run() {
    CRM_Utils_System::setTitle(ts('Dashboard'));
    parent::run();
  }
}
