<?php

use CRM_HRCore_CMSData_UserMailNotifierInterface as UserMailNotifierInterface;

/**
 * Class CRM_HRCore_CMSData_UserMailNotifier_Drupal
 */
class CRM_HRCore_CMSData_UserMailNotifier_Drupal implements UserMailNotifierInterface {

  /**
   * @var stdClass
   */
  private $user;

  /**
   * CRM_HRCore_CMSData_UserMailNotifier_Drupal constructor.
   *
   * @param array $contactData
   */
  public function __construct($contactData) {
    $this->user = user_load($contactData['cmsId']);
  }

  /**
   * {@inheritdoc}
   */
  public function sendWelcomeEmail() {
   return _user_mail_notify('register_admin_created', $this->user);
  }

  /**
   * {@inheritdoc}
   */
  public function sendPasswordResetEmail() {
    return _user_mail_notify('password_reset', $this->user);
  }
}
