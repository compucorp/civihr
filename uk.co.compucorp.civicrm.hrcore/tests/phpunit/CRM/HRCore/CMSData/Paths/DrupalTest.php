<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

use CRM_HRCore_CMSData_Paths_Drupal as DrupalPaths;

/**
 * Class CRM_HRCore_CMSData_Paths_DrupalTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_Paths_DrupalTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  /**
   * @var CRM_HRCore_CMSData_PathsInterface
   */
  protected $drupalPaths;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    $contactData = [ 'cmsId' => '4' ];
    $this->drupalPaths = new DrupalPaths($contactData);
  }

  public function testItReturnsTheDrupalDefaultImagePath() {
    $this->assertEquals($this->drupalPaths->getDefaultImagePath(), '/foo/bar/images/profile-default.png');
  }

  public function testItReturnsTheDrupalEditAccountLink() {
    $this->assertEquals($this->drupalPaths->getEditAccountPath(), '/user/4/edit');
  }

  public function testItReturnsTheDrupalLogoutLink() {
    $this->assertEquals($this->drupalPaths->getLogoutPath(), '/user/logout');
  }
}

/**
 * Mock of the original drupal function
 *
 * @return string
 */
function drupal_get_path() {
  return 'foo/bar';
}
