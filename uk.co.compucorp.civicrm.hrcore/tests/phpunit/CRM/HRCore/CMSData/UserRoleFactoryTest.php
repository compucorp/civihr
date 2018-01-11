<?php

use CRM_HRCore_CMSData_UserRoleFactory as UserRoleFactory;
use CRM_HRCore_CMSData_UserRoleInterface as UserRoleInterface;


/**
 * Class CRM_HRCore_CMSData_UserRoleFactoryTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_UserRoleFactoryTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testItReturnsAnInstanceOfTheExpectedClassWhenCMSIsSupported() {
    $contactData = ['cmsId' => 1];
    $userRole = UserRoleFactory::create('Drupal', $contactData);
    $this->assertInstanceOf(UserRoleInterface::class, $userRole);
  }

  public function testItThrowsAnExceptionWhenCMSIsNotSupported() {
    $cmsFramework = 'UnrecognizedCMS';
    $msg = sprintf('Unrecognized CMS: "%s"', $cmsFramework);
    $this->setExpectedException('Exception', $msg);

    UserRoleFactory::create($cmsFramework, []);
  }
}
