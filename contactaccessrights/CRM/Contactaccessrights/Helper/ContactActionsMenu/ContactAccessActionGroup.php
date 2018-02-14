<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_Contactaccessrights_BAO_Rights as ContactRights;
use CRM_HRCore_CMSData_UserPermissionInterface as CMSUserPermission;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;
use CRM_Contactaccessrights_Component_ContactActionsMenu_GroupTitleToolTipItem as GroupTitleToolTipItem;
use CRM_Contactaccessrights_Component_ContactActionsMenu_NoStaffTooltipItem as NoStaffToolTipItem;
use CRM_Contactaccessrights_Component_ContactActionsMenu_GenericListItem as GenericListItem;
use CRM_HRContactActionsMenu_Component_ParagraphItem as ParagraphItem;

class CRM_Contactaccessrights_Helper_ContactActionsMenu_ContactAccessActionGroup {

  /**
   * @var array
   */
  private $contactUserInfo;

  /**
   * @var ContactRights
   */
  private $contactRights;

  /**
   * @var array
   */
  private $contactACLGroups;

  /**
   * @var CMSUserPermission
   */
  private $cmsUserPermission;

  /**
   * CRM_Contactaccessrights_Helper_ContactActionsMenu_ContactAccessActionGroup constructor.
   *
   * @param array $contactUserInfo
   * @param ContactRights $contactRights
   * @param CMSUserPermission $cmsUserPermission
   * @param array $contactACLGroups
   */
  public function __construct(
    $contactUserInfo,
    ContactRights $contactRights,
    CMSUserPermission $cmsUserPermission,
    $contactACLGroups
  ) {
    $this->contactUserInfo = $contactUserInfo;
    $this->contactRights = $contactRights;
    $this->cmsUserPermission = $cmsUserPermission;
    $this->contactACLGroups = $contactACLGroups;
  }

  /**
   * Gets Contact Access Menu Group with menu items already
   * added.
   *
   * @return ActionsGroup
   */
  public function get() {
    $actionsGroup = new ActionsGroup($this->getGroupTitle());
    $hasAccessCivicrm = $this->hasPermission(['access CiviCRM']);

    if (!$hasAccessCivicrm) {
      return $actionsGroup;
    }

    $isAdmin = $this->hasPermission(['view all contacts', 'edit all contacts']);

    if ($isAdmin) {
      $allStaffItem =  new ParagraphItem('All Staff');
      $actionsGroup->addItem($allStaffItem);
    }

    if (!$isAdmin) {
      $regions = $this->getContactRegions();
      $locations = $this->getContactLocations();
      $aclGroups = $this->contactACLGroups;

      if ($regions) {
        $actionsGroup->addItem(new GenericListItem($regions, 'Regions'));
      }

      if ($locations) {
        $actionsGroup->addItem(new GenericListItem($locations, 'Locations'));
      }

      if ($aclGroups) {
        $actionsGroup->addItem(new GenericListItem($aclGroups, 'ACL Groups'));
      }

      if (empty($regions) && empty($locations) && empty($aclGroups)) {
        $toolTip = new NoStaffToolTipItem();
        $noStaffItem =  new ParagraphItem('No Staff ' . $toolTip->render());
        $actionsGroup->addItem($noStaffItem);
      }
    }

    $actionsGroup->addItem($this->getManageRegionalAccessButton());

    return $actionsGroup;
  }

  /**
   * Gets the Manage Regional Access button.
   *
   * @return ActionsGroupButtonItem
   */
  public function getManageRegionalAccessButton() {
    $params = [
      'label' => 'Manage Regional Access',
      'class' => 'btn-secondary',
      'url' => '',
      'icon' => ''
    ];

    $attributes = [
      'ng-controller' => 'AccessRightsController as accessRights',
      'ng-click' => 'accessRights.openModal()',
      'data-contact-access-rights' => ''
    ];

    return $this->getMenuButton($params, $attributes);
  }

  /**
   * Returns whether contact has the permissions or not.
   *
   * @return bool
   */
  private function hasPermission($permissions) {
    return $this->cmsUserPermission->check($this->contactUserInfo, $permissions);
  }

  /**
   * Returns an instance of an ActionsGroupButtonItem
   *
   * @param array $params
   * @param array $attributes
   *
   * @return ActionsGroupButtonItem
   */
  private function getMenuButton($params, array $attributes = []) {
    $button = new ActionsGroupButtonItem($params['label']);
    $button->setClass($params['class'])
      ->setIcon($params['icon'])
      ->setUrl($params['url']);

    foreach($attributes as $attribute => $value) {
      $button->setAttribute($attribute, $value);
    }

    return $button;
  }

  /**
   * Gets the Title for the Contact Access Action Group.
   *
   * @return string
   */
  private function getGroupTitle() {
    $groupTitleToolTip = new GroupTitleToolTipItem();

    return 'User Has Access To: ' . $groupTitleToolTip->render();
  }

  /**
   * Returns the regions the contact has rights to.
   *
   * @return array
   */
  private function getContactRegions() {
    $regions = $this->contactRights->getContactRightsByRegions($this->contactUserInfo['contact_id']);

    return array_column($regions, 'label');
  }

  /**
   * Returns the Locations that contact has rights to.
   *
   * @return array
   */
  private function getContactLocations() {
    $locations = $this->contactRights->getContactRightsByLocations($this->contactUserInfo['contact_id']);

    return array_column($locations, 'label');
  }
}
