<?php

trait CRM_HRCore_Test_Helpers_SessionHelpersTrait {

  protected function registerCurrentLoggedInContactInSession($contactID) {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);
  }

  protected function unregisterCurrentLoggedInContactFromSession() {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', NULL);
  }

  protected function setPermissions(array $permissions = []) {
    CRM_Core_Config::singleton()->userPermissionClass->permissions = $permissions;
  }

}
