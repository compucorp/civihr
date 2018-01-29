<?php

use CRM_HRCore_CMSData_Paths_Drupal as DrupalPaths;
use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;

/**
 * Class CRM_HRCore_CMSData_Paths_DrupalTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_Paths_DrupalTest extends BaseHeadlessTest {

  /**
   * @var CRM_HRCore_CMSData_Paths_PathsInterface
   */
  protected $drupalPaths;

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
