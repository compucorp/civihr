<?php

use CRM_HRCore_Helper_ExtensionHelper as ExtensionHelper;

/**
 * @group headless
 */
class ExtensionHelperTest extends CRM_HRCore_Test_BaseHeadlessTest {

  /**
   * @var string
   */
  private $extensionKey = 'uk.co.compucorp.civicrm.hremails';

  public function testCheckIsFalseAfterExtensionIsDisabled() {
    civicrm_api3('Extension', 'disable', ['keys' => $this->extensionKey]);
    $this->assertFalse(ExtensionHelper::isExtensionEnabled($this->extensionKey));
  }

  public function testCheckIsTrueAfterExtensionIsEnabled() {
    civicrm_api3('Extension', 'enable', ['keys' => $this->extensionKey]);
    $this->assertTrue(ExtensionHelper::isExtensionEnabled($this->extensionKey));
  }
}
