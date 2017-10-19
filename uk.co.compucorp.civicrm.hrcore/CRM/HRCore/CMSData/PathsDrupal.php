<?php
  class CRM_HRCore_CMSData_PathsDrupal implements CRM_HRCore_CMSData_PathsInterface {
    private $contactData;
    private $paths = [
      'defaultImage' =>  '/%{base}/images/profile-default.png',
      'edit' => '/user/%{userId}/edit',
      'logout' => '/user/logout'
    ];

    public function __construct($contactData) {
      $this->contactData = $contactData;
    }

    public function getDefaultImagePath() {
      $modulePath = drupal_get_path('module', 'civihr_employee_portal');

      return str_replace('%{base}', $modulePath, $this->paths['defaultImage']);
    }

    public function getEditAccountPath() {
      return str_replace('%{userId}', $this->contactData['cmsId'], $this->paths['edit']);
    }

    public function getLogoutPath() {
      return $this->paths['logout'];
    }
  }
