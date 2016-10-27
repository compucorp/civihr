<?php

require_once 'CRM/Core/Page.php';
require_once 'CRM/Styleguide/HtmlBuilder.php';

class CRM_Styleguide_Page_StyleGuide extends CRM_Core_Page {
  public function run() {
    CRM_Utils_System::setTitle(ts('Style Guide'));

    self::registerScripts();
    parent::run();
  }

  private static function registerScripts() {
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.styleguide', 'css/styleguide.css');
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.bootstrap', 'js/scrollspy.js', 1000);
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.bootstrap', 'js/dropdown.js', 1000);
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.styleguide', 'js/sg-plugins.js', 1000);
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.styleguide', 'js/sg-scripts.js', 1000);
  }
}
