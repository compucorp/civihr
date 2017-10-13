<?php

use CRM_HRLeaveAndAbsences_Service_InMemorySettingsManager as InMemorySettingsManager;

/**
 * Class CRM_HRLeaveAndAbsences_Service_InMemorySettingsManagerTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_InMemorySettingsManagerTest extends BaseHeadlessTest {

  /**
   * @var CRM_HRLeaveAndAbsences_Service_SettingsManager
   */
  private $settingsManager;

  public function setUp() {
    $this->settingsManager = new InMemorySettingsManager();
  }

  public function testItReturnsNullIfTheSettingDoesntExist() {
    $this->assertNull($this->settingsManager->get('whatever'));
  }

  /**
   * @dataProvider settingValuesInDifferentTypes
   */
  public function testItReturnsTheStoredSetting($value) {
    $setting = 'lorem_ipsum';
    $this->settingsManager->set($setting, $value);

    $this->assertEquals($value, $this->settingsManager->get($setting));
  }

  public function testItCanUpdateTheValueOfAnExistingSetting() {
    $setting = 'yadda';

    $this->settingsManager->set($setting, 'value 1');

    $this->assertEquals('value 1', $this->settingsManager->get($setting));

    $this->settingsManager->set($setting, 'value 2');

    $this->assertEquals('value 2', $this->settingsManager->get($setting));
  }

  public function settingValuesInDifferentTypes() {
    return [
      [1],
      ['2'],
      ['lambda, lambda, lambda'],
      [false],
      [true],
      [[1, 3, 'dasdsa', 4 => ['dasdsadsa']]],
    ];
  }
}
