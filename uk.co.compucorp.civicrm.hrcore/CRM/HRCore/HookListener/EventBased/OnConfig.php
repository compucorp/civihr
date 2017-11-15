<?php

class CRM_HRCore_HookListener_EventBased_OnConfig extends CRM_HRCore_HookListener_BaseListener {

  public function handle(&$config) {
    $this->updateCiviSettings();
    $this->addSmartyPluginDir();
  }

  private function updateCiviSettings() {
    global $civicrm_setting;
    $civicrm_setting['CiviCRM Preferences']['communityMessagesUrl'] = FALSE;
  }

  private function addSmartyPluginDir() {
    $smarty = CRM_Core_Smarty::singleton();
    $extensionPath = CRM_Core_Resources::singleton()->getPath('uk.co.compucorp.civicrm.hrcore');

    array_push($smarty->plugins_dir, $extensionPath . '/CRM/Smarty/plugins');
  }
}
