<?php

class CRM_HRCore_CMSData_PathsWordPress implements CRM_HRCore_CMSData_PathsInterface {
  private $contactData;

  public function __construct($contactData) {
    $this->contactData = $contactData;
  }

  public function getDefaultImagePath() {}
  public function getEditAccountPath() {}
  public function getLogoutPath() {}
}
