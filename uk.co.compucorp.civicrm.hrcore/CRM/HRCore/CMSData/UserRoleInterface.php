<?php

/**
 * Interface CRM_HRCore_CMSData_UserRoleInterface
 */
interface CRM_HRCore_CMSData_UserRoleInterface {

  /**
   * Returns the roles of the user object represented
   * in this class.
   *
   * @param bool $excludeAuthenticatedRole
   *   If true the authenticated user role will
   *   be excluded from the roles returned.
   *
   * @return array
   */
  public function getRoles($excludeAuthenticatedRole = FALSE);
}
