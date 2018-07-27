<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;
use CRM_HRCore_CMSData_Paths_PathsInterface as CMSUserPath;
use CRM_HRCore_CMSData_UserAccountInterface as CMSUserAccount;

/**
 * Class CRM_HRContactActionsMenu_Component_UserInformationLinkItem
 */
class CRM_HRContactActionsMenu_Component_UserInformationLinkItem implements ActionsGroupItemInterface {

  /**
   * @var CMSUserPath
   */
  private $cmsUserPath;
  /**
   * @var array
   */
  private $contactData;

  /**
   * @var CMSUserAccount
   */
  private $cmsUserAccount;

  /**
   * CRM_HRContactActionsMenu_Component_UserInformationLinkItem constructor.
   *
   * @param CMSUserPath $cmsUserPath
   * @param CMSUserAccount $cmsUserAccount
   * @param array $contactData
   */
  public function __construct(CMSUserPath $cmsUserPath, CMSUserAccount $cmsUserAccount, $contactData) {
    $this->cmsUserPath = $cmsUserPath;
    $this->cmsUserAccount = $cmsUserAccount;
    $this->contactData = $contactData;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   */
  public function render() {
    $userAccountDisabled = $this->cmsUserAccount->isUserDisabled($this->contactData);

    if ($userAccountDisabled) {
      $linkMarkup = '<a href="%s" class="disabled">%s</a>
        <strong class="text-warning"> (Account disabled)</strong>';
    }
    else {
      $linkMarkup = '<a href="%s">%s</a>';
    }

    $userInformationMarkup = '
      <div class="crm_contact-actions__user-info">
        <dl class="dl-horizontal dl-horizontal-inline">
          <dt>User:</dt>
          <dd>' . $linkMarkup . '</dd>
        </dl>
      </div>';

    return sprintf(
      $userInformationMarkup,
      $this->cmsUserPath->getEditAccountPath(),
      CRM_Utils_String::purifyHTML($this->contactData['name'])
    );
  }
}
