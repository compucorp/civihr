<?php

use CRM_HRCore_CMSData_Role_RoleServiceInterface as RoleServiceInterface;

class CRM_HRCore_CMSData_Role_DrupalRoleService implements RoleServiceInterface{

  /**
   * @var array
   *   A cache of the Drupal roles
   */
  private $roleList = [];

  /**
   * @inheritdoc
   */
  public function getRoleNames() {
    return user_roles();
  }

  /**
   * @inheritdoc
   */
  public function getLatestLoginByRole($roleName) {

  }

  /**
   * @inheritdoc
   */
  public function getRoleIds($roles) {
    if (empty($this->roleList)) {
      $this->roleList = user_roles(TRUE);
    }

    $roleIds = [];
    foreach ($this->roleList as $rid => $role) {
      if (in_array($role, $roles)) {
        $roleIds[$rid] = $rid;
      }
    }

    return $roleIds;
  }

}
