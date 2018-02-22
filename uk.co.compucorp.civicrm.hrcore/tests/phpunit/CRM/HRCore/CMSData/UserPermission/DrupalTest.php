<?php

use CRM_HRCore_CMSData_UserPermission_Drupal as DrupalUserPermissions;

/**
 * Class CRM_HRCore_CMSData_UserPermission_DrupalTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_UserPermission_DrupalTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testCheckPermissionsReturnsTrueWhenUserHasThePermission() {
    $contactData = ['cmsId' => 2];
    $userPermission = new DrupalUserPermissions();
    $result = $userPermission->check($contactData, ['sample permission']);

    $this->assertTrue($result);
  }

  public function testCheckPermissionsReturnsFalseWhenUserDoesNotHaveThePermission() {
    //setting a user ID of 0 will allow us to test the scenario for when a user
    //has no permission as this will return false when passed to the user_access
    //drupal mock function.

    $contactData = ['cmsId' => 0];
    $userPermission = new DrupalUserPermissions();
    $result = $userPermission->check($contactData, ['sample permission']);

    $this->assertFalse($result);
  }
}
