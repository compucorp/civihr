<?php

interface CRM_HRCore_CMSData_Role_RoleServiceInterface {

  /**
   * Fetches the system IDs for all the provided roles
   *
   * @param array $roles
   *
   * @return array
   *   The system IDs for the given roles
   */
  public function getRoleIds($roles);

  /**
   * @return array
   *   All the role names in the system.
   */
  public function getRoleNames();

  /**
   * Looks up the most recent login for given role
   *
   * @param string $roleName
   *
   * @return \DateTime|null
   *   The login date, null if role does not exist or no user has ever logged in
   */
  public function getLatestLoginByRole($roleName);
}
