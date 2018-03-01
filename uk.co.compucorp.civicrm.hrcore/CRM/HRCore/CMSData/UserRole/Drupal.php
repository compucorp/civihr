<?php

use CRM_HRCore_CMSData_UserRoleInterface as UserRoleInterface;

/**
 * Class CRM_HRCore_CMSData_UserRole_Drupal
 */
class CRM_HRCore_CMSData_UserRole_Drupal implements UserRoleInterface {

  /**
   * @var stdClass
   */
  private $user;

  /**
   * CRM_HRCore_CMSData_UserRole_Drupal constructor.
   *
   * @param array $contactData
   */
  public function __construct($contactData) {
    $this->user = user_load($contactData['cmsId']);
  }

  /**
   * {@inheritdoc}
   *
   * @return array
   */
  public function getRoles($excludeAuthenticatedRole = FALSE) {
    $roles = $this->user->roles;

    if ($excludeAuthenticatedRole) {
      unset($roles[DRUPAL_AUTHENTICATED_RID]);
    }

    return $roles;
  }
}
