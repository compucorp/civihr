<?php

use CRM_HRCore_Service_DrupalRoleService as DrupalRoleService;

class CRM_HRCore_Service_DrupalUserService {
  /**
   * @var DrupalRoleService
   */
  protected $roleService;

  /**
   * @var int
   */
  protected $loggedInUserID;

  /**
   * @param DrupalRoleService $roleService
   */
  public function __construct(DrupalRoleService $roleService) {
    $this->roleService = $roleService;
    $this->loggedInUserID = CRM_Core_Session::getLoggedInContactID();
  }

  /**
   * @param string $email
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

    /**
     * UFMatch is created by CiviCRM function civicrm_user_insert listening
     * for user_module_invoke('insert') from Drupal
     */
    $user = user_save(drupal_anonymous_user(), $params);

    return $user;
  }

  /**
   * @param $email
   */
  public function sendActivationMail($email) {
    $user = user_load_by_mail($email);
    if (!$user) {
      return;
    }

    _user_mail_notify('status_activated', $user);
  }

  /**
   * @param string $name
   *
   * @return bool
   */
  public function isValidUsername($name) {
    return empty(user_validate_name($name));
  }

  /**
   * @param string $email
   *
   * @return bool
   */
  public function isValidEmail($email) {
    return empty(user_validate_mail($email));
  }

}
