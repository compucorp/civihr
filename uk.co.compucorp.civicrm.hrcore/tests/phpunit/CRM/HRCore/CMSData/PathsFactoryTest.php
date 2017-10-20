<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

use CRM_HRCore_CMSData_PathsFactory as CMSPathsFactory;

/**
 * Class CRM_HRCore_CMSData_PathsFactoryTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_PathsFactoryTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testItReturnsAClassWithImplementingTheExpectedInterface() {
    $contactData = [];
    $pathsClass = CMSPathsFactory::create('Drupal', $contactData);

    $this->assertInstanceOf(CRM_HRCore_CMSData_PathsInterface::class, $pathsClass);
  }

  public function testItThrowsAnExceptionIfGivenAnUnrecognizedCMSName() {
    $cmsName = 'UnrecognizedCMS';

    $this->setExpectedException('Exception', "CMS \"{$cmsName}\" not recognized");

    $contactData = [];
    $pathsClass = CMSPathsFactory::create($cmsName, $contactData);
  }
}
