<?php

use CRM_HRContactActionsMenu_Component_UserRoleItem as UserRoleItem;
use CRM_HRCore_CMSData_UserRoleInterface as CMSUserRole;

/**
 * Class CRM_HRContactActionsMenu_ComponentUserRoleItemTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_ComponentUserRoleItemTest extends BaseHeadlessTest {

  public function testRender() {
    $userRoles = [1 => 'Fake Role1', 2 => 'Fake Role2'];
    $cmsUserRole = $this->prophesize(CMSUserRole::class);
    $cmsUserRole->getRoles()->willReturn($userRoles);

    $userRoleItem = new UserRoleItem($cmsUserRole->reveal());
    $roles = implode(', ', $userRoles);
    $expectedResult = 'Roles: ' . $roles;

    $this->assertEquals($expectedResult, $userRoleItem->render());
  }
}
