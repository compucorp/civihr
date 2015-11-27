<?php

require_once 'CRM/Core/Page.php';

class CRM_Appraisals_Page_Base extends CRM_Core_Page {
  function run() {
    /*if (!CRM_Core_Permission::check('access Appraisals')) {
        CRM_Core_Session::setStatus('Permission denied.', 'Appraisals', 'error');
        CRM_Utils_System::redirect('/civicrm');
        return FALSE;
    }*/
      
    parent::run();
  }

  static function registerScripts() {

    static $loaded = FALSE;
    if ($loaded) {
      return;
    }
    $loaded = TRUE;

    CRM_Core_Resources::singleton()
      ->addSettingsFactory(function () {
      global $user;
      $settings = array();
      $config = CRM_Core_Config::singleton();
      $extensions = CRM_Core_PseudoConstant::getExtensions();
      return array(
        'Appraisals' => array(
            'extensionPath' => CRM_Core_Resources::singleton()->getUrl('uk.co.compucorp.civicrm.appraisals'),
            'settings' => $settings,
            'permissions' => array(
            ),
        ),
        'adminId' => CRM_Core_Session::getLoggedInContactID(),
        'contactId' => CRM_Utils_Request::retrieve('cid', 'Integer'),
        'debug' => $config->debug,
      );
    });

  }
}
