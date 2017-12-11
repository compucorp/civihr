<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use CRM_HRCore_Helper_ExtensionHelper as ExtensionHelper;

/**
 * @group headless
 */
class ExtensionHelperTest extends PHPUnit_Framework_TestCase
  implements HeadlessInterface, TransactionalInterface {

  /**
   * Install hrcore when setting up
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testAfterDisabling() {
    $hrCoreKey = 'uk.co.compucorp.civicrm.hrcore';
    $this->assertTrue(ExtensionHelper::isExtensionEnabled($hrCoreKey));
    civicrm_api3('Extension', 'disable', ['keys' => $hrCoreKey]);
    $this->assertFalse(ExtensionHelper::isExtensionEnabled($hrCoreKey));
  }
}
