<?php

use CRM_HRCore_CMSData_Role_RoleServiceInterface as RoleServiceInterface;

/**
 * Implementation of RoleServiceInterface to interact with a Drupal 7 system
 */
class CRM_HRCore_CMSData_Role_DrupalRoleService implements RoleServiceInterface {

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

    $roleNames = $this->getRoleMachineNames();
    $rolesToExclude = ['authenticated user', 'anonymous user'];
    $roleNames = array_diff($roleNames, $rolesToExclude);
    $returnArray = array_fill_keys($roleNames, NULL);

    foreach ($result as $rid => $loginTimestamp) {
      if (isset($roleNames[$rid])) {
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

  /**
   * Gets the machine names for roles that have them. Roles without machine
   * names, such as 'anonymous user' will default to the role name
   *
   * @return array
   *   The array will be in the format 'rid' => 'machine_name'
   */
  private function getRoleMachineNames() {
    $result = db_select('role', 'r')
      ->fields('r', ['rid', 'name', 'machine_name'])
      ->execute()
      ->fetchAllAssoc('rid', PDO::FETCH_BOTH);

    // use name if machine_name is not set
    array_walk($result, function (&$role) {
      $role['machine_name'] = $role['machine_name'] ?: $role['name'];
    });

    return array_column($result, 'machine_name', 'rid');
  }

}
