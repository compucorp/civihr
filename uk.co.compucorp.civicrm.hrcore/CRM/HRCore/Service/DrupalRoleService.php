<?php

class DrupalRoleService {
  /**
   * @var array
   */
  private $roleList = [];

  /**
   * @param array $roles
   *
   * @return array
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
