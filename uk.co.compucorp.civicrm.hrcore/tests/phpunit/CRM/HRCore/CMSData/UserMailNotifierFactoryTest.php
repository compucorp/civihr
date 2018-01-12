<?php

use CRM_HRCore_CMSData_UserMailNotifierFactory as UserMailNotifierFactory;
use CRM_HRCore_CMSData_UserMailNotifierInterface as UserMailNotifierInterface;

/**
 * Class CRM_HRCore_CMSData_CMSUserMailNotifierFactoryTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_CMSUserMailNotifierFactoryTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testItReturnsAnInstanceOfTheExpectedClassWhenCMSIsSupported() {
    //We need to manually set this value because civicrm sets it to UnitTests
    //when running in testing environment.
    CRM_Core_Config::singleton()->userFramework = 'Drupal';
    $mailNotifier = UserMailNotifierFactory::create();
    $this->assertInstanceOf(UserMailNotifierInterface::class, $mailNotifier);
  }

  public function testItThrowsAnExceptionWhenCMSIsNotSupported() {
    //We need to manually set this value because civicrm sets it to UnitTests
    //when running in testing environment.
    $cmsFramework = 'UnrecognizedCMS';
    CRM_Core_Config::singleton()->userFramework = $cmsFramework;
    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    $this->setExpectedException('Exception', $msg);

    UserMailNotifierFactory::create();
  }
}

