<?php

use CRM_HRCore_CMSData_UserRole_Drupal as DrupalUserRole;

/**
 * Class CRM_HRCore_CMSData_UerRole_DrupalTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_UserRole_DrupalTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testGetUserRoles() {
    $contactData = ['cmsId' => 1];
    $userRoleService = new DrupalUserRole($contactData);
    $expectedRoles = [1 => 'Fake Role'];
    $this->assertEquals($expectedRoles, $userRoleService->getRoles());
  }
}
