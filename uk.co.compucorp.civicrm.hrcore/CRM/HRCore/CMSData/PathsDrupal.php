<?php

class CRM_HRCore_CMSData_PathsDrupal implements CRM_HRCore_CMSData_PathsInterface {

  /**
   * The contact data used to build the paths
   *
   * @var array
   */
  private $contactData;

  /**
   * The Drupal paths
   *
   * @var array
   */
  private $paths = [
    'defaultImage' =>  '/%{base}/images/profile-default.png',
    'edit' => '/user/%{userId}/edit',
    'logout' => '/user/logout'
  ];

  /**
   * @param array $contactData
   */
  public function __construct($contactData) {
    $this->contactData = $contactData;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultImagePath() {
    $modulePath = drupal_get_path('module', 'civihr_employee_portal');

    return str_replace('%{base}', $modulePath, $this->paths['defaultImage']);
  }

  /**
   * {@inheritdoc}
   */
  public function getEditAccountPath() {
    return str_replace('%{userId}', $this->contactData['cmsId'], $this->paths['edit']);
  }

  /**
   * {@inheritdoc}
   */
  public function getLogoutPath() {
    return $this->paths['logout'];
  }
}
