<?php

use CRM_HRContactActionsMenu_Component_Group as ActionsGroup;
use CRM_HRContactActionsMenu_Component_Menu as ActionsMenu;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;
use CRM_HRContactActionsMenu_Component_UserInformationLinkItem as UserInformationLinkItem;
use CRM_HRContactActionsMenu_Component_UserRoleItem as UserRoleItem;
use CRM_HRCore_CMSData_PathsInterface as CMSUserPath;
use CRM_HRCore_CMSData_UserRoleInterface as CMSUserRole;

/**
 * Class CRM_HRContactActionsMenu_Helper_UserInformationMenuGroup
 */
class CRM_HRContactActionsMenu_Helper_UserInformationMenuGroup {

  const SEND_PASSWORD_RESET_EMAIL_PATH = 'civicrm/contactactionsmenu/sendpasswordresetmail';
  const SEND_WELCOME_EMAIL_PATH = 'civicrm/contactactionsmenu/sendwelcomemail';
  const CREATE_CMS_USER_PATH = 'civicrm/user/create-account';

  /**
   * Adds the User Information Group menu items to the
   * highlighted panel of the contact actions menu.
   *
   * @param \CRM_HRContactActionsMenu_Component_Menu $menu
   * @param array $contactUserInfo
   *   Contact user info gotten from the contact helper
   * @param CMSUserPath $cmsUserPath
   * @param CMSUserRole $cmsUserRole)
   *
   * @return \CRM_HRContactActionsMenu_Component_Menu
   *
   * @throws \Exception
   */
  public static function addToMenu(ActionsMenu $menu, $contactUserInfo, $cmsUserPath, $cmsUserRole) {
    $contactID = $contactUserInfo['contact_id'];
    $actionsGroup = new ActionsGroup('User Information:');

    if(!empty($contactUserInfo['id'])) {
      $contactUserInfo['cmsId'] = $contactUserInfo['id'];
      $userInformationLinkItem = new UserInformationLinkItem($cmsUserPath, $contactUserInfo);
      $actionsGroup->addItem($userInformationLinkItem);

      $userRoleItem = new UserRoleItem($cmsUserRole);
      $actionsGroup->addItem($userRoleItem);

      $actionsGroup->addItem(self::getSendWelcomeMailButton($contactID));
      $actionsGroup->addItem(self::getSendPasswordResetButton($contactID));
    } else {
      $actionsGroup->addItem(self::getCreateUserButton($contactID));
    }

    $menu->addToHighlightedPanel($actionsGroup);

    return $menu;
  }

  /**
   * Returns an instance of an ActionsGroupButtonItem
   *
   * @param array $params
   *
   * @return \CRM_HRContactActionsMenu_Component_GroupButtonItem
   */
  private static function getMenuButton($params) {
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
  private static function getSendWelcomeMailButton($contactID) {
    $url = CRM_Utils_System::url(self::SEND_WELCOME_EMAIL_PATH, "cid=$contactID");
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
  private static function getSendPasswordResetButton($contactID) {
    $url = CRM_Utils_System::url(self::SEND_PASSWORD_RESET_EMAIL_PATH, "cid=$contactID");
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
  private static function getCreateUserButton($contactID) {
    $url = CRM_Utils_System::url(self::CREATE_CMS_USER_PATH, "cid=$contactID");
    $params = [
      'label' => 'CREATE A USER FOR THIS STAFF MEMBER',
      'class' => 'tbd',
      'icon' => 'tbd',
      'url' => $url
    ];

    return self::getMenuButton($params);
  }
}
