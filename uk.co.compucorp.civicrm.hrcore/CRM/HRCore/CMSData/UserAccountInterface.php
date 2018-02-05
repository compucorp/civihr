<?php

/**
 * Interface CRM_HRCore_CMSData_UserAccountInterface
 *
 * This interface will be extended by the CMS class
 * that wants to provide functionality for taking
 * some actions/providing some information on a user
 * account.
 */
interface CRM_HRCore_CMSData_UserAccountInterface {

  /**
   * Cancel User account.
   *
   * @param array $contactData
   *
   * @return mixed
   */
  public function cancel($contactData);

  /**
   * Disable User account.
   *
   * @param array $contactData
   *
   * @return mixed
   */
  public function disable($contactData);

  /**
   * Enable user account.
   *
   * @param array $contactData
   *
   * @return mixed
   */
  public function enable($contactData);

  /**
   * Checks if user account is disabled
   *
   * @param array $contactData
   *
   * @return boolean
   */
  public function isUserDisabled($contactData);
}
