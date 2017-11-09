<?php

class CRM_HRCore_Listener_Form_Admin_Extensions extends CRM_HRCore_Listener_AbstractListener {

  protected $objectClass = 'CRM_Admin_Form_Extensions';

  public function onBuildForm() {
    if (!$this->canHandle()) {
      return;
    }

    $extensionKey= CRM_Utils_Request::retrieve('key', 'String');

    if ($extensionKey == 'uk.co.compucorp.civicrm.hrsampledata') {
      $title = ts("Be Careful");
      $message = ts("Installing/Uninstalling this extension will remove all existing data, so make sure to create a backup first !");

      CRM_Core_Session::setStatus($message, $title, 'no-popup crm-error', ['expires' => 0]);
    }
  }
}
