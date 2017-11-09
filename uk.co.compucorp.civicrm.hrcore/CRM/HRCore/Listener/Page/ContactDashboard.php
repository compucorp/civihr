<?php

class CRM_HRCore_Listener_Page_ContactDashboard extends CRM_HRCore_Listener_Page_AbstractPage {

  protected $pageClass = 'CRM_Contact_Page_DashBoard';

  public function onPageRun() {
    if (!$this->canHandle()) {
      return;
    }

    CRM_Utils_System::setTitle(ts('CiviHR Home'));
  }
}
