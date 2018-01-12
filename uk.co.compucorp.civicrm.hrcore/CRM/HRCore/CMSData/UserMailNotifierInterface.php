<?php

/**
 * Interface CRM_HRCore_CMSData_UserMailNotifierInterface
 *
 * This interface will be extended by the CMS class
 * that wants to provide functionality for sending emails notifications
 * (mainly password reset and welcome emails).
 */
interface CRM_HRCore_CMSData_UserMailNotifierInterface {

  /**
   * Get the user object.
   *
   * @param array $contactData
   *
   * @return object
   */
  public function getUser($contactData);

  /**
   * Sends a welcome email to the user.
   *
   * @param object $user
   *
   * @return mixed
   */
  public function sendWelcomeEmail($user);

  /**
   * Sends a password reset email to the user.
   *
   * @param object $user
   *
   * @return mixed
   */
  public function sendPasswordResetEmail($user);
}
