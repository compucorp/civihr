<?php

require_once 'CRM/Core/Page.php';

class CRM_Styleguide_Page_StyleGuide extends CRM_Core_Page {
  public function run() {
    CRM_Utils_System::setTitle(ts('StyleGuide'));

    self::registerScripts();
    parent::run();
  }

  private static function registerScripts() {
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.styleguide', 'css/styleguide.css');
  }
}
