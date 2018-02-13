<?php

use CRM_HRCore_CMSData_UserPermissionInterface as UserPermissionInterface;

/**
 * Class CRM_HRCore_CMSData_UserPermission_Drupal
 */
class CRM_HRCore_CMSData_UserPermission_Drupal implements UserPermissionInterface{

  /**
   * Gets the Drupal user object
   *
   * @param array $contactData
   *
   * @return \stdClass
   */
  private function getUser($contactData) {
    return user_load($contactData['cmsId']);
  }

  /**
   * {@inheritdoc}
   */
  public function check($contactData, array $permissions = []) {
    $user = $this->getUser($contactData);

    foreach($permissions as $permission) {
      if (user_access($permission, $user)) {
        return TRUE;
      }
    }

    return FALSE;
  }
}
