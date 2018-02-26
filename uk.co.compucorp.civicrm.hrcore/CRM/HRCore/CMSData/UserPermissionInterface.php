<?php

interface CRM_HRCore_CMSData_UserPermissionInterface {

  /**
   * Checks if the user has any of the permissions
   * in permissions array.
   *
   * @param array $contactData
   * @param array $permissions
   *
   * @return bool
   */
  public function check($contactData, array $permissions = []);
}
