<?php

require_once 'CRM/Core/Page.php';

class CRM_Hrjobroles_Page_JobRoles extends CRM_Core_Page {

  function run() {
    CRM_Utils_System::setTitle(ts('JobRoles'));

    self::registerScripts();
    parent::run();
  }

  private static function registerScripts() {
    CRM_Core_Resources::singleton()->addVars('hrjobroles', array(
      'baseURL' => CRM_Extension_System::singleton()->getMapper()->keyToUrl('com.civicrm.hrjobroles'),
      'path' => CRM_Core_Resources::singleton()->getUrl('com.civicrm.hrjobroles')
    ));

    CRM_Core_Resources::singleton()->addScriptFile('com.civicrm.hrjobroles', 'js/dist/job-roles.min.js', 1010);
    CRM_Core_Resources::singleton()->addStyleFile('com.civicrm.hrjobroles', 'css/hrjobroles.css');
  }
}
