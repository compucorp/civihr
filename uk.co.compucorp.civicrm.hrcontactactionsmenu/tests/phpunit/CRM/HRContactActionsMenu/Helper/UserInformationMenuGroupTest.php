<?php

use CRM_HRContactActionsMenu_Helper_UserInformationMenuGroup as UserInformationMenuGroupHelper;
use CRM_HRContactActionsMenu_Component_Menu as ActionsMenu;
use CRM_HRCore_CMSData_UserRoleInterface as CMSUserRole;
use CRM_HRCore_CMSData_PathsInterface as CMSUserPath;

/**
 * Class CRM_HRContactActionsMenu_Helper_UserInformationMenuGroupTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Helper_UserInformationMenuGroupTest extends BaseHeadlessTest {

  public function testMenuItemsAreAddedToTheHighlightedPanelWhenContactHasNoCMSUser() {
    $contactUserInfo = ['contact_id' => 2];
    $actionsMenu = new ActionsMenu();
    $cmsUserPath = $this->prophesize(CMSUserPath::class);
    $cmsUserRole = $this->prophesize(CMSUserRole::class);
    $actionsMenu = UserInformationMenuGroupHelper::addToMenu(
      $actionsMenu,
      $contactUserInfo,
      $cmsUserPath->reveal(),
      $cmsUserRole->reveal()
    );

    $highlightedPanelItems = $actionsMenu->getHighlightedPanelItems();
    //Only one item should be added to the highlighted panel
    $this->assertCount(1, $highlightedPanelItems);
    $actionGroup = array_shift($highlightedPanelItems);

    //check that the group title is correct
    $this->assertEquals('User Information:', $actionGroup->getTitle());
    //since user has no cms account, only one item is expected, the button
    //to create cms user for contact
    $this->assertCount(1, $actionGroup->getItems());

    //Make sure that the function does not add any item to the main panel.
    $this->assertEmpty($actionsMenu->getMainPanelItems());
  }

  public function testMenuItemsAreAddedToTheHighlightedPanelWhenContactHasCMSUser() {
    $contactUserInfo = ['id' => 3, 'name' => 'cms username', 'contact_id' => 2];
    $actionsMenu = new ActionsMenu();
    $cmsUserPath = $this->prophesize(CMSUserPath::class);
    $cmsUserRole = $this->prophesize(CMSUserRole::class);
    $actionsMenu = UserInformationMenuGroupHelper::addToMenu(
      $actionsMenu,
      $contactUserInfo,
      $cmsUserPath->reveal(),
      $cmsUserRole->reveal()
    );

    $highlightedPanelItems = $actionsMenu->getHighlightedPanelItems();
    //Only one item should be added to the highlighted panel
    $this->assertCount(1, $highlightedPanelItems);
    $actionGroup = array_shift($highlightedPanelItems);

    //check that the group title is correct
    $this->assertEquals('User Information:', $actionGroup->getTitle());

    //since user has a cms account, four items are expected, the user info link,
    //the user role link, send password reset and send welcome email buttons.
    $this->assertCount(4, $actionGroup->getItems());

    //Make sure that the function does not add any item to the main panel.
    $this->assertEmpty($actionsMenu->getMainPanelItems());
  }
}
