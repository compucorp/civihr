<?php

require_once 'CRM/Core/Page.php';

class CRM_Contactsummary_Page_ContactSummary extends CRM_Core_Page {

  function run() {
    CRM_Utils_System::setTitle(ts('ContactSummary'));

    self::registerScripts();
    parent::run();
  }

  private static function registerScripts() {
    CRM_Core_Resources::singleton()
      ->addVars('contactsummary', array(
        'baseURL' => CRM_Extension_System::singleton()->getMapper()->keyToUrl('org.civicrm.contactsummary')))
      ->addScriptFile('org.civicrm.contactsummary', 'js/dist/contact-summary.min.js', 1005)
      ->addStyleFile('org.civicrm.contactsummary', 'css/contactsummary.css');
  }
}
