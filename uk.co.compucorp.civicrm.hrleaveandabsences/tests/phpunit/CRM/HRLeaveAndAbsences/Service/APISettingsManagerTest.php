<?php

use CRM_HRLeaveAndAbsences_Service_APISettingsManager as APISettingsManager;

/**
 * Class CRM_HRLeaveAndAbsences_Service_InMemorySettingsManagerTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_APISettingsManagerTest extends BaseHeadlessTest {

  /**
   * @var CRM_HRLeaveAndAbsences_Service_SettingsManager
   */
  private $settingsManager;

  public function setUp() {
    $this->settingsManager = new APISettingsManager();
  }

  public function testItReturnsNullIfTheSettingDoesntExist() {
    $this->assertNull($this->settingsManager->get('whatever'));
  }

  /**
   * @dataProvider settingValuesInDifferentTypes
   */
  public function testItReturnsTheStoredSetting($value) {
    $setting = 'lorem_ipsum';

    // It's very hard to test things that use the Setting API. It requires a
    // complete system/cache flush which is very slow to do in a test. For this
    // reason, we mock the API 'create' and 'get' methods here in a way to
    // check that the Service will call the right actions with the right params
    $apiKernel = \Civi::service('civi_api_kernel');
    $adhocProvider = new \Civi\API\Provider\AdhocProvider(3, 'Setting');
    $apiKernel->registerApiProvider($adhocProvider);

    $adhocProvider->addAction('create', '', function ($apiRequest) use($value, $setting) {
      $this->assertEquals($value, $apiRequest['params'][$setting]);

      return civicrm_api3_create_success([
        $setting => $value
      ]);
    });

    $adhocProvider->addAction('get', '', function ($apiRequest) use($value, $setting) {
      $this->assertEquals(1, $apiRequest['params']['sequential']);
      $this->assertEquals([$setting], $apiRequest['params']['return']);

      return civicrm_api3_create_success([
        [$setting => $value]
      ], [], 'Setting', 'get');
    });

    $this->settingsManager->set($setting, $value);

    $this->assertEquals($value, $this->settingsManager->get($setting));
  }

  public function settingValuesInDifferentTypes() {
    return [
      [1],
      ['2'],
      ['lambda, lambda, lambda'],
      [false],
      [true],
      [[1, 3, 'dasdsa', 4 => ['dasdsadsa']]]
    ];
  }
}
