<?php

use CRM_HRCore_Helper_ExtensionHelper as ExtensionHelper;

/**
 * @group headless
 */
class ExtensionHelperTest extends CRM_HRCore_Test_BaseHeadlessTest {

  /**
   * @var string
   */
  private $hrCoreKey = 'uk.co.compucorp.civicrm.hrcore';

  public function testCheckIsFalseAfterExtensionIsDisabled() {
    // hrcore is enabled by default in CRM_HRCore_Test_BaseHeadlessTest
    civicrm_api3('Extension', 'disable', ['keys' => $this->hrCoreKey]);
    $this->assertFalse(ExtensionHelper::isExtensionEnabled($this->hrCoreKey));
  }

  public function testCheckIsTrueAfterExtensionIsEnabled() {
    civicrm_api3('Extension', 'enable', ['keys' => $this->hrCoreKey]);
    $this->assertTrue(ExtensionHelper::isExtensionEnabled($this->hrCoreKey));
  }
}
