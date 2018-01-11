<?php

/**
 * Interface CRM_HRCore_CMSData_UserRoleInterface
 */
interface CRM_HRCore_CMSData_UserRoleInterface {

  /**
   * Returns the roles of the user object represented
   * in this class.
   *
   * @return array
   */
  public function getRoles();
}
