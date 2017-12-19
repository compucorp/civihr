<?php

use CRM_HRCore_CMSData_Role_RoleServiceInterface as RoleServiceInterface;

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
  public function getLatestLoginByRole($roleName) {
    $rids = $this->getRoleIds([$roleName]);
    $rid = array_shift($rids);

    if (empty($rid)) {
      throw new \Exception(sprintf('Role "%s" not found', $roleName));
    }

    $query = db_select('users', 'u');
    $query->addExpression('MAX(u.login)');
    $query->leftJoin('users_roles', 'ur', 'ur.uid = u.uid');
    $query->where('ur.rid = :rid', ['rid' => $rid]);

    $result = $query->execute()->fetchCol();
    $latestLoginTimestamp = array_shift($result);

    if ($latestLoginTimestamp == 0) {
      return NULL;
    }

    $latestLoginDate = new \DateTime();
    $latestLoginDate->setTimestamp($latestLoginTimestamp);

    return $latestLoginDate;
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
