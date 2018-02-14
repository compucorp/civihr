<?php

use CRM_HRCore_CMSData_UserAccountFactory as UserAccountFactory;
use CRM_HRCore_CMSData_UserAccountInterface as UserAccountInterface;

/**
 * Class CRM_HRCore_CMSData_UserAccountFactoryTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_UserAccountFactoryTest extends CRM_HRCore_Test_BaseHeadlessTest {

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
    $userAccount= UserAccountFactory::create();
    $this->assertInstanceOf(UserAccountInterface::class, $userAccount);
  }

  public function testItThrowsAnExceptionWhenCMSIsNotSupported() {
    //We need to manually set this value because civicrm sets it to some other value
    //when running in testing environment.
    $cmsFramework = 'UnrecognizedCMS';
    CRM_Core_Config::singleton()->userFramework = $cmsFramework;
    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    $this->setExpectedException('Exception', $msg);

    UserAccountFactory::create();
  }
}
