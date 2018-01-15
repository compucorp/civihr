<?php

use CRM_HRCore_CMSData_Role_RoleServiceInterface as RoleServiceInterface;

/**
 * Implementation of RoleServiceInterface to interact with a Drupal 7 system
 */
class CRM_HRCore_CMSData_Role_DrupalRoleService implements RoleServiceInterface{

  /**
   * @inheritdoc
   */
  public function getRoleNames() {
    return user_roles();
  }

  /**
   * @inheritdoc
   */
  public function getLatestLoginByRole() {
    $query = db_select('users', 'u');
    $query->fields('ur', ['rid']);
    $query->addExpression('MAX(u.login)');
    $query->leftJoin('users_roles', 'ur', 'ur.uid = u.uid');
    $query->groupBy('ur.rid');

    $result = $query->execute()->fetchAllKeyed();

    $roleNames = $this->getRoleNames();
    unset($roleNames['authenticated user'], $roleNames['anonymous user']);
    $returnArray = array_fill_keys($roleNames, NULL);

    foreach ($result as $rid => $loginTimestamp) {
      if (array_key_exists($rid, $roleNames)) {
        $roleName = $roleNames[$rid];
        $loginDate = NULL;
        if ($loginTimestamp != 0) {
          $loginDate = new \DateTime();
          $loginDate->setTimestamp($loginTimestamp);
        }
        $returnArray[$roleName] = $loginDate;
      }
    }

    return $returnArray;
  }

  /**
   * @inheritdoc
   */
  public function getRoleIds($roles) {
    $roleList = user_roles(TRUE);

    $roleIds = [];
    foreach ($roleList as $rid => $role) {
      if (in_array($role, $roles)) {
        $roleIds[$rid] = $rid;
      }
    }

    return $roleIds;
  }

}
