<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;
use CRM_HRCore_CMSData_UserRoleInterface as CMSUserRole;

/**
 * Class CRM_HRContactActionsMenu_Component_UserRoleItem
 */
class CRM_HRContactActionsMenu_Component_UserRoleItem implements ActionsGroupItemInterface {

  /**
   * @var CMSUserRole
   */
  private $cmsUserRole;

  /**
   * CRM_HRContactActionsMenu_Component_UserRoleItem constructor.
   *
   * @param CMSUserRole $cmsUserRole
   */
  public function __construct(CMSUserRole $cmsUserRole) {
    $this->cmsUserRole = $cmsUserRole;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   */
  public function render() {
    $roles = implode(', ', $this->cmsUserRole->getRoles());

    return 'Roles: ' . $roles;
  }
}