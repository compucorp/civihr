<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;
use CRM_HRContactActionsMenu_Component_UserInformationLinkItem as UserInformationLinkItem;
use CRM_HRContactActionsMenu_Component_UserRoleItem as UserRoleItem;
use CRM_HRCore_CMSData_PathsInterface as CMSUserPath;
use CRM_HRCore_CMSData_UserRoleInterface as CMSUserRole;

/**
 * Class CRM_HRContactActionsMenu_Helper_UserInformationActionGroup
 */
class CRM_HRContactActionsMenu_Helper_UserInformationActionGroup {

  /**
   * @var array
   */
  private $contactUserInfo;
  /**
   * @var \CRM_HRCore_CMSData_PathsInterface
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
   * @param CMSUserPath $cmsUserPath
   * @param CMSUserRole $cmsUserRole
   *
   */
  public function __construct($contactUserInfo, $cmsUserPath, $cmsUserRole) {
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

      $actionsGroup->addItem(self::getSendWelcomeMailButton($contactID));
      $actionsGroup->addItem(self::getSendPasswordResetButton($contactID));
    } else {
      $actionsGroup->addItem(self::getCreateUserButton($contactID));
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
      ->setUrl($params['url']);

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
      'label' => 'SEND WELCOME EMAIL',
      'class' => 'tbd',
      'icon' => 'tbd',
      'url' => $url
    ];

    return self::getMenuButton($params);
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
      'label' => 'SEND PASSWORD RESET EMAIL',
      'class' => 'tbd',
      'icon' => 'tbd',
      'url' => $url
    ];

    return self::getMenuButton($params);
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
      'label' => 'CREATE A USER FOR THIS STAFF MEMBER',
      'class' => 'tbd',
      'icon' => 'tbd',
      'url' => $url
    ];

    return self::getMenuButton($params);
  }
}
