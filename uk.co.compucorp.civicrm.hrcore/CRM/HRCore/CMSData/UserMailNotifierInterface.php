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
   * Sends a welcome email to the user object represented in this
   * class.
   *
   * @return mixed
   */
  public function sendWelcomeEmail();

  /**
   * Sends a password reset email to the user object represented in
   * this class.
   *
   * @return mixed
   */
  public function sendPasswordResetEmail();
}
