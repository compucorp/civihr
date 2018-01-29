<?php

class CRM_HRCore_Test_Helpers_SessionHelper {

  public static function registerCurrentLoggedInContactInSession($contactID) {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);
  }

  public static function unregisterCurrentLoggedInContactFromSession() {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', NULL);
  }

  public static function setPermissions(array $permissions = []) {
    CRM_Core_Config::singleton()->userPermissionClass->permissions = $permissions;
  }

}
