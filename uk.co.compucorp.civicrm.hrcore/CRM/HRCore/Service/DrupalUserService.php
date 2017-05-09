<?php

class DrupalUserService {
  /**
   * @var DrupalRoleService
   */
  protected $roleService;

  public function __construct() {
    // todo move to container get()
    $this->roleService = new DrupalRoleService();
  }

  /**
   * @param $email
   * @param bool $active
   * @param array $roles
   *
   * @return object
   */
  public function createNew($email, $active = FALSE, $roles = []) {
    $params = [
      'name' => $email,
      'pass' => user_password(), // random password
      'mail' => $email,
      'status' => $active ? 1 : 0,
      'access' => REQUEST_TIME,
    ];

    if ($roles) {
      $params['roles'] = $this->roleService->getRoleIds($roles);
    }

    return user_save(drupal_anonymous_user(), $params);
  }

  /**
   * @param $original
   * @param array $roles
   *
   * @return object
   */
  public function addRoles($original, $roles = []) {
    $roles = $this->roleService->getRoleIds($roles);
    $roles = array_merge($roles, $original->roles);

    return user_save($original, ['roles' => $roles]);
  }

}
