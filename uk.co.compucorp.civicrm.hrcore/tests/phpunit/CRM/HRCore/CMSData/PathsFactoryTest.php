<?php

use CRM_HRCore_CMSData_PathsFactory as CMSPathsFactory;
use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;

/**
 * Class CRM_HRCore_CMSData_PathsFactoryTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_PathsFactoryTest extends BaseHeadlessTest {

  public function testItReturnsAClassWithImplementingTheExpectedInterface() {
    $contactData = [];
    $pathsClass = CMSPathsFactory::create('Drupal', $contactData);

    $this->assertInstanceOf(CRM_HRCore_CMSData_Paths_PathsInterface::class, $pathsClass);
  }

  public function testItThrowsAnExceptionIfGivenAnUnrecognizedCMSName() {
    $cmsName = 'UnrecognizedCMS';

    $this->setExpectedException('Exception', "CMS \"{$cmsName}\" not recognized");

    $contactData = [];
    CMSPathsFactory::create($cmsName, $contactData);
  }

}
