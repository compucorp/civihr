<?php

/**
 * CRM_HRLeaveAndAbsences_Page_AbsenceTab
 */
class CRM_HRLeaveAndAbsences_Page_AbsenceTab extends  CRM_Core_Page {
  public function run() {
    CRM_Utils_System::setTitle(ts('Absence'));

    self::registerScripts();
    parent::run();
  }


  static function registerScripts() {
    static $loaded = FALSE;

    if ($loaded) {
      return;
    }

    $loaded = TRUE;

    CRM_Core_Resources::singleton()
      ->addStyleUrl('http://'.$_SERVER['HTTP_HOST'].'/sites/all/themes/civihr_employee_portal_theme/civihr_default_theme/assets/css/civihr_default_theme.style.css');
  }
}
