<?php

use CRM_HRCore_CMSData_UserPermissionFactory as UserPermissionFactory;
use CRM_HRCore_CMSData_UserPermissionInterface as UserPermissionInterface;

/**
 * Class CRM_HRCore_CMSData_UserPermissionFactoryTest
 *
 * @group headless
 */
class UserPermissionFactoryTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testItReturnsAnInstanceOfTheExpectedClassWhenCMSIsSupported() {
    $previousUserFramework = CRM_Core_Config::singleton()->userFramework;
    //We need to manually set this value because civicrm sets it to some other value
    //when running in testing environment.
    CRM_Core_Config::singleton()->userFramework = 'Drupal';
    $userPermission = UserPermissionFactory::create();
    $this->assertInstanceOf(UserPermissionInterface::class, $userPermission);
    CRM_Core_Config::singleton()->userFramework = $previousUserFramework;
  }

  public function testItThrowsAnExceptionWhenCMSIsNotSupported() {
    $previousUserFramework = CRM_Core_Config::singleton()->userFramework;
    //We need to manually set this value because civicrm sets it to some other value
    //when running in testing environment.
    $cmsFramework = 'UnrecognizedCMS';
    CRM_Core_Config::singleton()->userFramework = $cmsFramework;
    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    $this->setExpectedException('Exception', $msg);

    UserPermissionFactory::create();
    CRM_Core_Config::singleton()->userFramework = $previousUserFramework;
  }
}
