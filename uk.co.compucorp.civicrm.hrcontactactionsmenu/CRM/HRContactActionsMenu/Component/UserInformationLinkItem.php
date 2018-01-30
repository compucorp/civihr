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
      <div class="crm_contact-actions__user-info">
        <dl class="dl-horizontal dl-horizontal-inline">
          <dt>User:</dt>
          <dd><a href="%s" class="text-primary">%s</a></dd>
        </dl>
      </div>';

    return sprintf(
      $userInformationMarkup,
      $this->cmsUserPath->getEditAccountPath(),
      $this->contactData['cmsId'] . ' ' . $this->contactData['name']
    );
  }
}
