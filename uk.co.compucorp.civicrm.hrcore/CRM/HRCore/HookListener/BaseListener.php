<?php

class CRM_HRCore_HookListener_BaseListener {

  public static function onConfig(&$config) {
    self::updateCiviSettings();
    self::addSmartyPluginDir();
  }

  protected function isExtensionEnabled($key) {
    $isEnabled = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_Extension',
      $key,
      'is_active',
      'full_name'
    );

    return !empty($isEnabled) ? true : false;
  }

  private static function updateCiviSettings() {
    global $civicrm_setting;
    $civicrm_setting['CiviCRM Preferences']['communityMessagesUrl'] = FALSE;
  }

  private static function addSmartyPluginDir() {
    $smarty = CRM_Core_Smarty::singleton();
    $extensionPath = CRM_Core_Resources::singleton()->getPath('uk.co.compucorp.civicrm.hrcore');

    array_push($smarty->plugins_dir, $extensionPath . '/CRM/Smarty/plugins');
  }
}
