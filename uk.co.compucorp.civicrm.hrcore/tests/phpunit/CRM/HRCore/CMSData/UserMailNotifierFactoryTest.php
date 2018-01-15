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
    $previousUserFramework = CRM_Core_Config::singleton()->userFramework;
    //We need to manually set this value because civicrm sets it to some other value
    //when running in testing environment.
    CRM_Core_Config::singleton()->userFramework = 'Drupal';
    $mailNotifier = UserMailNotifierFactory::create();
    $this->assertInstanceOf(UserMailNotifierInterface::class, $mailNotifier);
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

    UserMailNotifierFactory::create();
    CRM_Core_Config::singleton()->userFramework = $previousUserFramework;
  }
}

