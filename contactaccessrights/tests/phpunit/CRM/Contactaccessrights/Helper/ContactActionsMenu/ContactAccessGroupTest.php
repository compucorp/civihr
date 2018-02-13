<?php

use CRM_HRCore_CMSData_UserPermissionInterface as CMSUserPermission;
use CRM_Contactaccessrights_Service_ContactRights as ContactRightsService;
use CRM_Contactaccessrights_Helper_ContactActionsMenu_ContactAccessActionGroup as ContactAccessActionGroup;
use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;
use CRM_Contactaccessrights_Component_ContactActionsMenu_GroupTitleToolTipItem as GroupTitleToolTipItem;
use CRM_Contactaccessrights_Component_ContactActionsMenu_GenericTextItem as GenericTextItem;
use CRM_Contactaccessrights_Component_ContactActionsMenu_UserRegionsListItem as UserRegionsListItem;
use CRM_Contactaccessrights_Component_ContactActionsMenu_UserLocationsListItem as UserLocationsListItem;
use CRM_Contactaccessrights_Component_ContactActionsMenu_UserACLGroupsListItem as UserACLGroupsListItem;

/**
 * Class CRM_Contactaccessrights_Helper_ContactAccessGroupTest
 *
 * @group headless
 */
class CRM_Contactaccessrights_Helper_ContactAccessGroupTest extends BaseHeadlessTest {

  public function testMenuItemsAreCorrectlyAddedWhenContactIsAnAdmin() {
    $contactUserInfo = ['cmsId' => 4, 'contact_id' => 5];
    $aclGroups = [];
    $userPermission = $this->prophesize(CMSUserPermission::class);
    $userPermission->check($contactUserInfo, ['view all contacts', 'edit all contacts'])->willReturn(TRUE);
    $contactRightsService = $this->prophesize(ContactRightsService::class);

    $contactAccessActionGroup = new ContactAccessActionGroup(
      $contactUserInfo,
      $contactRightsService->reveal(),
      $userPermission->reveal(),
      $aclGroups
    );

    $contactAccessActionGroup = $contactAccessActionGroup->get();
    $menuItems = $contactAccessActionGroup->getItems();
    //Two Items are expected: The All staff Item and the Manage Regional Access Button
    $this->assertCount(2, $menuItems);
    $this->assertInstanceOf(GenericTextItem::class, $menuItems[0]);
    $this->assertInstanceOf(ActionsGroupButtonItem::class, $menuItems[1]);

    $this->assertEquals($contactAccessActionGroup->getTitle(), $this->getGroupTitle());
  }

  public function testMenuItemsAreCorrectlyAddedWhenContactIsNotAnAdminAndHasAccessRights() {
    $contactUserInfo = ['cmsId' => 4, 'contact_id' => 5];
    $aclGroups = ['Group 1'];
    $userPermission = $this->prophesize(CMSUserPermission::class);
    $userPermission->check($contactUserInfo, ['view all contacts', 'edit all contacts'])->willReturn(FALSE);
    $contactRightsService = $this->prophesize(ContactRightsService::class);
    $contactRightsService->getContactRightsByRegions($contactUserInfo['contact_id'])->willReturn([['label' => 'Region1']]);
    $contactRightsService->getContactRightsByLocations($contactUserInfo['contact_id'])->willReturn([['label' => 'Location1']]);

    $contactAccessActionGroup = new ContactAccessActionGroup(
      $contactUserInfo,
      $contactRightsService->reveal(),
      $userPermission->reveal(),
      $aclGroups
    );

    $contactAccessActionGroup = $contactAccessActionGroup->get();
    $menuItems = $contactAccessActionGroup->getItems();
    //Four Items are expected: Region List, Locations List, ACL Group
    //List item and the Manage Regional Access Button
    $this->assertCount(4, $menuItems);
    $this->assertInstanceOf(UserRegionsListItem::class, $menuItems[0]);
    $this->assertInstanceOf(UserLocationsListItem::class, $menuItems[1]);
    $this->assertInstanceOf(UserACLGroupsListItem::class, $menuItems[2]);
    $this->assertInstanceOf(ActionsGroupButtonItem::class, $menuItems[3]);

    $this->assertEquals($contactAccessActionGroup->getTitle(), $this->getGroupTitle());
  }

  public function testMenuItemsAreCorrectlyAddedWhenContactIsNotAnAdminAndDoesNotHaveAccessRights() {
    $contactUserInfo = ['cmsId' => 4, 'contact_id' => 5];
    $aclGroups = [];
    $userPermission = $this->prophesize(CMSUserPermission::class);
    $userPermission->check($contactUserInfo, ['view all contacts', 'edit all contacts'])->willReturn(FALSE);
    $contactRightsService = $this->prophesize(ContactRightsService::class);
    $contactRightsService->getContactRightsByRegions($contactUserInfo['contact_id'])->willReturn([]);
    $contactRightsService->getContactRightsByLocations($contactUserInfo['contact_id'])->willReturn([]);

    $contactAccessActionGroup = new ContactAccessActionGroup(
      $contactUserInfo,
      $contactRightsService->reveal(),
      $userPermission->reveal(),
      $aclGroups
    );

    $contactAccessActionGroup = $contactAccessActionGroup->get();
    $menuItems = $contactAccessActionGroup->getItems();
    //Two Items are expected: No Staff Text Item and the Manage Regional Access Button
    $this->assertCount(2, $menuItems);
    $this->assertInstanceOf(GenericTextItem::class, $menuItems[0]);
    $this->assertInstanceOf(ActionsGroupButtonItem::class, $menuItems[1]);

    $this->assertEquals($contactAccessActionGroup->getTitle(), $this->getGroupTitle());
  }

  public function testMenuItemsAreAddedCorrectlyWhenContactHasOtherAccessButNotRegionsAccess() {
    $contactUserInfo = ['cmsId' => 4, 'contact_id' => 5];
    $aclGroups = ['Group 1'];
    $userPermission = $this->prophesize(CMSUserPermission::class);
    $userPermission->check($contactUserInfo, ['view all contacts', 'edit all contacts'])->willReturn(FALSE);
    $contactRightsService = $this->prophesize(ContactRightsService::class);
    $contactRightsService->getContactRightsByRegions($contactUserInfo['contact_id'])->willReturn();
    $contactRightsService->getContactRightsByLocations($contactUserInfo['contact_id'])->willReturn([['label' => 'Location1']]);

    $contactAccessActionGroup = new ContactAccessActionGroup(
      $contactUserInfo,
      $contactRightsService->reveal(),
      $userPermission->reveal(),
      $aclGroups
    );

    $contactAccessActionGroup = $contactAccessActionGroup->get();
    $menuItems = $contactAccessActionGroup->getItems();
    //Three Items are expected: Locations List, ACL Group
    //List item and the Manage Regional Access Button
    $this->assertCount(3, $menuItems);
    $this->assertInstanceOf(UserLocationsListItem::class, $menuItems[0]);
    $this->assertInstanceOf(UserACLGroupsListItem::class, $menuItems[1]);
    $this->assertInstanceOf(ActionsGroupButtonItem::class, $menuItems[2]);

    $this->assertEquals($contactAccessActionGroup->getTitle(), $this->getGroupTitle());
  }

  public function testMenuItemsAreAddedCorrectlyWhenContactHasOtherAccessButNotLocationsAccess() {
    $contactUserInfo = ['cmsId' => 4, 'contact_id' => 5];
    $aclGroups = ['Group 1'];
    $userPermission = $this->prophesize(CMSUserPermission::class);
    $userPermission->check($contactUserInfo, ['view all contacts', 'edit all contacts'])->willReturn(FALSE);
    $contactRightsService = $this->prophesize(ContactRightsService::class);
    $contactRightsService->getContactRightsByRegions($contactUserInfo['contact_id'])->willReturn([['label' => 'Region1']]);
    $contactRightsService->getContactRightsByLocations($contactUserInfo['contact_id'])->willReturn([]);

    $contactAccessActionGroup = new ContactAccessActionGroup(
      $contactUserInfo,
      $contactRightsService->reveal(),
      $userPermission->reveal(),
      $aclGroups
    );

    $contactAccessActionGroup = $contactAccessActionGroup->get();
    $menuItems = $contactAccessActionGroup->getItems();
    //Three Items are expected: Regions List, ACL Group
    //List item and the Manage Regional Access Button
    $this->assertCount(3, $menuItems);
    $this->assertInstanceOf(UserRegionsListItem::class, $menuItems[0]);
    $this->assertInstanceOf(UserACLGroupsListItem::class, $menuItems[1]);
    $this->assertInstanceOf(ActionsGroupButtonItem::class, $menuItems[2]);

    $this->assertEquals($contactAccessActionGroup->getTitle(), $this->getGroupTitle());
  }

  public function testMenuItemsAreAddedCorrectlyWhenContactHasOtherAccessButNotACLGroupsAccess() {
    $contactUserInfo = ['cmsId' => 4, 'contact_id' => 5];
    $aclGroups = [];
    $userPermission = $this->prophesize(CMSUserPermission::class);
    $userPermission->check($contactUserInfo, ['view all contacts', 'edit all contacts'])->willReturn(FALSE);
    $contactRightsService = $this->prophesize(ContactRightsService::class);
    $contactRightsService->getContactRightsByRegions($contactUserInfo['contact_id'])->willReturn([['label' => 'Region1']]);
    $contactRightsService->getContactRightsByLocations($contactUserInfo['contact_id'])->willReturn([['label' => 'Location1']]);

    $contactAccessActionGroup = new ContactAccessActionGroup(
      $contactUserInfo,
      $contactRightsService->reveal(),
      $userPermission->reveal(),
      $aclGroups
    );

    $contactAccessActionGroup = $contactAccessActionGroup->get();
    $menuItems = $contactAccessActionGroup->getItems();
    //Three Items are expected: Regions List, Locations
    //List item and the Manage Regional Access Button
    $this->assertCount(3, $menuItems);
    $this->assertInstanceOf(UserRegionsListItem::class, $menuItems[0]);
    $this->assertInstanceOf(UserLocationsListItem::class, $menuItems[1]);
    $this->assertInstanceOf(ActionsGroupButtonItem::class, $menuItems[2]);

    $this->assertEquals($contactAccessActionGroup->getTitle(), $this->getGroupTitle());
  }

  private function getGroupTitle() {
    $groupTitleToolTip = new GroupTitleToolTipItem();

    return 'User Has Access To: ' . $groupTitleToolTip->render();
  }
}
