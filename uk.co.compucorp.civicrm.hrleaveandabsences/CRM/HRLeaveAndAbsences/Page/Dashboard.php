<?php

/**
 * CRM_HRLeaveAndAbsences_Page_Dashboard
 */
class CRM_HRLeaveAndAbsences_Page_Dashboard extends CRM_Core_Page {
  public function run() {
    CRM_Utils_System::setTitle(ts('Dashboard'));

    CRM_Core_Resources::singleton()
      ->addStyleFile('uk.co.compucorp.civicrm.hrleaveandabsences', 'css/leaveandabsence.css');

    parent::run();
  }
}
