<?php

class CRM_StyleGuide_Page_StyleGuide extends CRM_Core_Page {

  /**
   * @param array $path
   *   List of path elements.
   *
   * @return void
   * @throws CRM_Core_Exception
   */
  public function run($path = array()) {
    CRM_Utils_System::setTitle(ts('Style Guide'));

    if (empty($path[2])) {
      throw new \CRM_Core_Exception("The path must specify the name of the style-guide.");
    }
    $styleguide = Civi::service('style_guides')->get($path[2]);
    if ($styleguide === NULL) {
      throw new \CRM_Core_Exception("The specified style-guide does not exist.");
    }
    $this->assign('styleguide', $styleguide);

    self::registerScripts();
    parent::run();
  }

  private static function registerScripts() {
    CRM_Core_Resources::singleton()->addStyleFile('org.civicrm.styleguide', 'css/styleguide.css');
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.styleguide', 'js/sg-plugins.js', 1000, 'html-header');
    CRM_Core_Resources::singleton()->addScriptFile('org.civicrm.styleguide', 'js/sg-scripts.js', 1000);
  }
}
