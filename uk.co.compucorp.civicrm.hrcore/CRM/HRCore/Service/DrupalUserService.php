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
   * @param int $id
   * @param string $email
   * @param bool $active
   * @param array $roles
   *
   * @return object
   */
  public function createNew($id, $email, $active = FALSE, $roles = []) {
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
    $this->createActivity($id, 'Create User Account');

    return $user;
  }

  /**
   * @param int $contactID
   * @param string $email
   */
  public function sendActivationMail($contactID, $email) {
    $user = user_load_by_mail($email);
    if (!$user) {
      return;
    }

    _user_mail_notify('status_activated', $user);
    $this->createActivity($contactID, 'Send Onboarding Email');
  }

  /**
   * @param int $contactID
   * @param string $type
   */
  private function createActivity($contactID, $type) {
    civicrm_api3('Activity', 'create', [
      'activity_type_id' => $type,
      'source_contact_id' => $this->loggedInUserID, // who did it
      'target_id' => $contactID, // who is it for
    ]);
  }

}
