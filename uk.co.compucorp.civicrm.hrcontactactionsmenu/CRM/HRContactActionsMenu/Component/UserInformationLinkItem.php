<?php

use CRM_HRContactActionsMenu_Component_GroupItem as ActionsGroupItemInterface;
use CRM_HRCore_CMSData_PathsInterface as CMSUserPath;

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
    $link = sprintf(
      '<a href="%s" class="%s">%s</a>',
      $this->cmsUserPath->getEditAccountPath(),
      'tbd',
      $this->contactData['cmsId'] . ' ' . $this->contactData['name']
    );

    return 'User: ' . $link;
  }
}
