<?php

use CRM_HRCore_CMSData_UserPermissionFactory as UserPermissionFactory;
use CRM_HRCore_CMSData_UserPermissionInterface as UserPermissionInterface;

/**
 * Class CRM_HRCore_CMSData_UserPermissionFactoryTest
 *
 * @group headless
 */
class UserPermissionFactoryTest extends CRM_HRCore_Test_BaseHeadlessTest {

  private $previousUserFramework;

  public function setUp() {
    $this->previousUserFramework = CRM_Core_Config::singleton()->userFramework;
  }

  public function tearDown() {
    CRM_Core_Config::singleton()->userFramework = $this->previousUserFramework;
  }

  public function testItReturnsAnInstanceOfTheExpectedClassWhenCMSIsSupported() {
    //We need to manually set this value because civicrm sets it to some other value
    //when running in testing environment.
    CRM_Core_Config::singleton()->userFramework = 'Drupal';
    $userPermission = UserPermissionFactory::create();
    $this->assertInstanceOf(UserPermissionInterface::class, $userPermission);
  }

  public function testItThrowsAnExceptionWhenCMSIsNotSupported() {
    //We need to manually set this value because civicrm sets it to some other value
    //when running in testing environment.
    $cmsFramework = 'UnrecognizedCMS';
    CRM_Core_Config::singleton()->userFramework = $cmsFramework;
    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    $this->setExpectedException('Exception', $msg);

    UserPermissionFactory::create();
  }

}
