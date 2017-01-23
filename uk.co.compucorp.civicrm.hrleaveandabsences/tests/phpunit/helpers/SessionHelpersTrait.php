<?php

trait CRM_HRLeaveAndAbsences_SessionHelpersTrait {

  private function registerCurrentLoggedInContactInSession($contactID) {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);
  }
}
