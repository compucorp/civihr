<?php

/**
 * Implement this interface to interact with user roles on a certain CMS
 */
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
   * Looks up the most recent login for all system roles
   *
   * @return array
   *   The login dates for each most recent user login, indexed by role. Login
   *   date will be NULL if no user with that role has ever logged in.
   */
  public function getLatestLoginByRole();
}
