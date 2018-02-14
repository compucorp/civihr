<?php

use CRM_HRContactActionsMenu_Helper_UserInformationActionGroup as UserInformationActionGroupHelper;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as GroupButtonItem;
use CRM_HRContactActionsMenu_Component_UserInformationLinkItem as UserInformationLinkItem;
use CRM_HRContactActionsMenu_Component_UserRoleItem as UserRoleItem;
use CRM_HRCore_CMSData_UserRoleInterface as CMSUserRole;
use CRM_HRCore_CMSData_Paths_PathsInterface as CMSUserPath;
use CRM_HRContactActionsMenu_Component_ParagraphItem as ParagraphItem;

/**
 * Class CRM_HRContactActionsMenu_Helper_UserInformationActionGroupTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Helper_UserInformationActionGroupTest extends BaseHeadlessTest {

  public function testMenuItemsAreCorrectlyAddedWhenContactHasNoCMSUser() {
    $contactUserInfo = ['contact_id' => 2];
    $cmsUserPath = $this->prophesize(CMSUserPath::class);
    $cmsUserRole = $this->prophesize(CMSUserRole::class);
    $userActionGroupHelper = new UserInformationActionGroupHelper(
      $contactUserInfo,
      $cmsUserPath->reveal(),
      $cmsUserRole->reveal()
    );

    $userActionGroup = $userActionGroupHelper->get();

    //since user has no cms account, only two items are expected,
    // the no user text item and the button to create cms user for contact
    $userActionGroupItems = $userActionGroup->getItems();
    $this->assertCount(2, $userActionGroupItems);
    $this->assertInstanceOf(ParagraphItem::class, $userActionGroupItems[0]);
    $this->assertInstanceOf(GroupButtonItem::class, $userActionGroupItems[1]);

    //check that the group title is correct
    $this->assertEquals('User Information:', $userActionGroup->getTitle());
  }

  public function testMenuItemsAreCorrectlyAddedWhenContactHasCMSUser() {
    $contactUserInfo = ['cmsId' => 3, 'name' => 'cms username', 'contact_id' => 2];
    $cmsUserPath = $this->prophesize(CMSUserPath::class);
    $cmsUserRole = $this->prophesize(CMSUserRole::class);
    $userActionGroupHelper= new UserInformationActionGroupHelper(
      $contactUserInfo,
      $cmsUserPath->reveal(),
      $cmsUserRole->reveal()
    );

    $userActionGroup = $userActionGroupHelper->get();

    //since user has a cms account, four items are expected, the user info link,
    //the user role link, send password reset and send welcome email buttons.
    $userActionGroupItems = $userActionGroup->getItems();
    $this->assertCount(4, $userActionGroupItems);

    $this->assertInstanceOf(UserInformationLinkItem::class, $userActionGroupItems[0]);
    $this->assertInstanceOf(UserRoleItem::class, $userActionGroupItems[1]);
    $this->assertInstanceOf(GroupButtonItem::class, $userActionGroupItems[2]);
    $this->assertInstanceOf(GroupButtonItem::class, $userActionGroupItems[3]);

    //check that the group title is correct
    $this->assertEquals('User Information:', $userActionGroup->getTitle());
  }
}
