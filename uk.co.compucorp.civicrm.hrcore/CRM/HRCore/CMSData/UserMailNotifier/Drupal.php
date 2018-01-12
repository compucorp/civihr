<?php

use CRM_HRCore_CMSData_UserMailNotifierInterface as UserMailNotifierInterface;

/**
 * Class CRM_HRCore_CMSData_UserMailNotifier_Drupal
 */
class CRM_HRCore_CMSData_UserMailNotifier_Drupal implements UserMailNotifierInterface {

  /**
   *{@inheritdoc}
   *
   * @return \stdClass
   */
  public function getUser($contactData) {
    return user_load($contactData['cmsId']);
  }

  /**
   * {@inheritdoc}
   */
  public function sendWelcomeEmail($user) {
   return _user_mail_notify('register_admin_created', $user);
  }

  /**
   * {@inheritdoc}
   */
  public function sendPasswordResetEmail($user) {
    return _user_mail_notify('password_reset', $user);
  }
}
