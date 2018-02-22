<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;
use CRM_HRCore_CMSData_UserRoleInterface as CMSUserRole;
use CRM_HRCore_CMSData_Paths_PathsInterface as CMSUserPath;

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
  public function __construct(CMSUserRole $cmsUserRole, CMSUserPath $cmsUserPath) {
    $this->cmsUserRole = $cmsUserRole;
    $this->cmsUserPath = $cmsUserPath;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   */
  public function render() {
    $roles = implode(', ', $this->cmsUserRole->getRoles());
    $userPath = $this->cmsUserPath->getEditAccountPath();

    $userRolesMarkup = '
      <div class="crm_contact-actions__user-info">
        <dl class="dl-horizontal dl-horizontal-inline">
          <dt>Roles:</dt>
          <dd>%s</dd>
        </dl>
        <a class="crm_contact-actions__edit-roles" href="%s">
          <i class="fa fa-edit"></i>
        </a>
      </div>';

    return sprintf(
      $userRolesMarkup,
      $roles,
      $userPath
    );
  }
}
