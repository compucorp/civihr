<?php

use CRM_HRCore_Service_DrupalRoleService as DrupalRoleService;

class CRM_HRCore_Service_DrupalUserService {
  /**
   * @var DrupalRoleService
   */
  protected $roleService;

  /**
   * @var
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

    $user = user_save(drupal_anonymous_user(), $params);
    $this->createActivity($user, 'Create User Account');

    return $user;
  }

  /**
   * @param object $user
   */
  public function sendActivationMail($user) {
    _user_mail_notify('status_activated', $user);
    $this->createActivity($user, 'Send Onboarding Email');
  }

  /**
   * @param object $user
   * @param string $type
   */
  private function createActivity($user, $type) {
    civicrm_api3('Activity', 'create', [
      'activity_type_id' => $type,
      'source_contact_id' => $this->loggedInUserID, // who did it
      'target_id' => $this->getContactId($user), // who is it for
    ]);
  }

  /**
   * Gets the contact for a certain user
   *
   * @param $user
   *
   * @return int
   */
  private function getContactId($user) {
    $result = civicrm_api3('UFMatch', 'getsingle', [
      'return' => ["contact_id"],
      'uf_id' => $user->uid,
    ]);
    return (int) CRM_Utils_Array::value('contact_id', $result);
  }

}
