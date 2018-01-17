<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;
use CRM_HRCore_CMSData_Paths_PathsInterface as CMSUserPath;

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
   * CRM_HRContactActionsMenu_Component_UserInformationLinkItem constructor.
   *
   * @param CMSUserPath $cmsUserPath
   * @param array $contactData
   */
  public function __construct(CMSUserPath $cmsUserPath, $contactData) {
    $this->cmsUserPath = $cmsUserPath;
    $this->contactData = $contactData;
  }

  /**
   * {@inheritdoc}
   *
   * @return string
   */
  public function render() {
    $userInformationMarkup = '
      <p><span class="crm_contact_action_menu__bold_text">User: </span> 
        <a href="%s" class="text-primary">%s</a>
      </p>';

    return sprintf(
      $userInformationMarkup,
      $this->cmsUserPath->getEditAccountPath(),
      $this->contactData['cmsId'] . ' ' . $this->contactData['name']
    );
  }
}
