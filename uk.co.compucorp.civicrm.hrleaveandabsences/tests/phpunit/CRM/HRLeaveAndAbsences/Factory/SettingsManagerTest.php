<?php

use CRM_HRLeaveAndAbsences_Factory_SettingsManager as SettingsManagerFactory;
use CRM_HRLeaveAndAbsences_Service_APISettingsManager as APISettingsManager;
use CRM_HRLeaveAndAbsences_Service_InMemorySettingsManager as InMemorySettingsManager;

/**
 * Class CRM_HRLeaveAndAbsences_Factory_SettingsManagerTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_SettingsManagerTest extends BaseHeadlessTest {

  public function testItCreatesAnAPISettingsManagerByDefault() {
    $settingsManager = SettingsManagerFactory::create();

    $this->assertInstanceOf(APISettingsManager::class, $settingsManager);
  }

  public function testItCreatesAnInMemorySettingsManagerWhenInMemoryParamIsTrue() {
    $settingsManager = SettingsManagerFactory::create(true);

    $this->assertInstanceOf(InMemorySettingsManager::class, $settingsManager);
  }
}
