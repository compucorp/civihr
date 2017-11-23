<?php

interface CRM_HRCore_CMSData_PathsInterface {

  /**
   * Returns the path of the default image
   *
   * @return string
   */
  public function getDefaultImagePath();

  /**
   * Returns the path of the page for editing the
   * currently logged-in user
   *
   * @return string
   */
  public function getEditAccountPath();

  /**
   * Returns the path for logging out of the system
   *
   * @return string
   */
  public function getLogoutPath();
}
