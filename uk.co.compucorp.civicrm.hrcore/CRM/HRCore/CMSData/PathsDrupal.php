<?php

class CRM_HRCore_CMSData_PathsDrupal implements CRM_HRCore_CMSData_PathsInterface {

  /**
   * @const string
   */
  const DEFAULT_IMAGE_PATH = '/%{base}/images/profile-default.png';

  /**
   * @const string
   */
  const EDIT_PATH = '/user/%{userId}/edit';

  /**
   * @const string
   */
  const LOGOUT_PATH = '/user/logout';

  /**
   * The contact data used to build the paths
   *
   * @var array
   */
  private $contactData;

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

    return str_replace('%{base}', $modulePath, self::DEFAULT_IMAGE_PATH);
  }

  /**
   * {@inheritdoc}
   */
  public function getEditAccountPath() {
    return str_replace('%{userId}', $this->contactData['cmsId'], self::EDIT_PATH);
  }

  /**
   * {@inheritdoc}
   */
  public function getLogoutPath() {
    return self::LOGOUT_PATH;
  }
}
