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
    $contactData = ['cmsId' => 1];
    $mailNotifier = UserMailNotifierFactory::create('Drupal', $contactData);
    $this->assertInstanceOf(UserMailNotifierInterface::class, $mailNotifier);
  }

  public function testItThrowsAnExceptionWhenCMSIsNotSupported() {
    $cmsFramework = 'UnrecognizedCMS';
    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    $this->setExpectedException('Exception', $msg);

    UserMailNotifierFactory::create($cmsFramework, []);
  }
}

