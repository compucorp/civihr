<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;
use CRM_HRContactActionsMenu_Component_UserInformationLinkItem as UserInformationLinkItem;
use CRM_HRContactActionsMenu_Component_UserRoleItem as UserRoleItem;
use CRM_HRCore_CMSData_Paths_PathsInterface as CMSUserPath;
use CRM_HRCore_CMSData_UserRoleInterface as CMSUserRole;
use CRM_HRContactActionsMenu_Component_NoUserTextItem as NoUserTextItem;

/**
 * Class CRM_HRContactActionsMenu_Helper_UserInformationActionGroup
 */
class CRM_HRContactActionsMenu_Helper_UserInformationActionGroup {

  /**
   * @var array
   */
  private $contactUserInfo;
  /**
   * @var \CRM_HRCore_CMSData_Paths_PathsInterface
   */
  private $cmsUserPath;
  /**
   * @var \CRM_HRCore_CMSData_UserRoleInterface
   */
  private $cmsUserRole;

  /**
   * CRM_HRContactActionsMenu_Helper_UserInformationActionGroup constructor.
   *
   * @param array $contactUserInfo
   *   Contact user info gotten from the contact helper
   * @param CMSUserPath|null $cmsUserPath
   * @param CMSUserRole|null $cmsUserRole
   *
   */
  public function __construct($contactUserInfo, $cmsUserPath = null, $cmsUserRole = null) {
    $this->contactUserInfo = $contactUserInfo;
    $this->cmsUserRole = $cmsUserRole;
    $this->cmsUserPath = $cmsUserPath;
  }

  /**
   * Gets User Information Menu Group with menu items already
   * added.
   *
   * @return ActionsGroup
   */
  public function get() {
    $contactID = $this->contactUserInfo['contact_id'];
    $actionsGroup = new ActionsGroup('User Information:');

    if(!empty($this->contactUserInfo['cmsId'])) {
      $userInformationLinkItem = new UserInformationLinkItem($this->cmsUserPath, $this->contactUserInfo);
      $actionsGroup->addItem($userInformationLinkItem);

      $userRoleItem = new UserRoleItem($this->cmsUserRole);
      $actionsGroup->addItem($userRoleItem);

      $actionsGroup->addItem($this->getSendWelcomeMailButton($contactID));
      $actionsGroup->addItem($this->getSendPasswordResetButton($contactID));
    } else {
      $noUserTextItem = new NoUserTextItem();
      $actionsGroup->addItem($noUserTextItem);
      $actionsGroup->addItem($this->getCreateUserButton($contactID));
    }

    return $actionsGroup;
  }

  /**
   * Returns an instance of an ActionsGroupButtonItem
   *
   * @param array $params
   *
   * @return \CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  private function getMenuButton($params) {
    $button = new ActionsGroupButtonItem($params['label']);
    $button->setClass($params['class'])
      ->setIcon($params['icon'])
      ->setUrl($params['url'])
      ->addBottomMargin();

    return $button;
  }

  /**
   * Gets the send welcome email button item
   *
   * @param int $contactID
   *
   * @return \CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  private function getSendWelcomeMailButton($contactID) {
    $url = CRM_Utils_System::url('civicrm/contactactionsmenu/sendwelcomemail', "cid=$contactID");
    $params = [
      'label' => 'Send Welcome Email',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-envelope-o',
      'url' => $url
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the send password reset button item
   *
   * @param int $contactID
   *
   * @return \CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  private function getSendPasswordResetButton($contactID) {
    $url = CRM_Utils_System::url('civicrm/contactactionsmenu/sendpasswordresetmail', "cid=$contactID");
    $params = [
      'label' => 'Send Password Reset Email',
      'class' => 'btn btn-primary-outline',
      'icon' => 'fa fa-envelope-o',
      'url' => $url
    ];

    return $this->getMenuButton($params);
  }

  /**
   * Gets the create user button item.
   *
   * @param int $contactID
   *
   * @return \CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  private function getCreateUserButton($contactID) {
    $url = CRM_Utils_System::url('civicrm/user/create-account', "cid=$contactID");
    $params = [
      'label' => 'Create a user for this staff member',
      'class' => 'btn btn-primary',
      'icon' => 'fa fa-plus',
      'url' => $url
    ];

    return $this->getMenuButton($params);
  }
}
