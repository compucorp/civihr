<?php

use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;
use CRM_HRCore_Service_CustomGroupStatusSwitcher as CustomGroupStatusSwitcher;
use CRM_HRCore_Test_Fabricator_CustomGroup as CustomGroupFabricator;
use CRM_HRCore_Test_Fabricator_CustomField as CustomFieldFabricator;

/**
 * @group headless
 */
class CRM_HRCore_Service_CustomGroupStatusSwitcherTest extends BaseHeadlessTest {

  /**
   * @var array
   */
  private static $customGroup = [];

  /**
   * @var array
   */
  private static $customFields = [];

  public function testDisablingWillDisableAllFields() {
    $groupId = self::$customGroup['id'];

    $switcher = new CustomGroupStatusSwitcher();
    $switcher->disable(self::$customGroup['name']);

    // check that all custom fields were enabled
    foreach (self::$customFields as $customField) {
      $id = $customField['id'];
      $updatedField = civicrm_api3('CustomField', 'getsingle', ['id' => $id]);
      $this->assertEquals(0, $updatedField['is_active']);
    }

    // check custom group was enabled
    $updatedGroup = civicrm_api3('CustomGroup', 'getsingle', ['id' => $groupId]);
    $this->assertEquals(0, $updatedGroup['is_active']);

  }

  public function testEnablingWillEnableAllFields() {
    $groupId = self::$customGroup['id'];
    // disable the group
    civicrm_api3('CustomGroup', 'create', ['id' => $groupId, 'is_active' => 0]);

    // disable the fields
    foreach (self::$customFields as $customField) {
      civicrm_api3('CustomField', 'create', [
        'id' => $customField['id'],
        'is_active' => 0,
      ]);
    }

    $switcher = new CustomGroupStatusSwitcher();
    $switcher->enable(self::$customGroup['name']);

    // check that all custom fields were enabled
    foreach (self::$customFields as $customField) {
      $id = $customField['id'];
      $updatedField = civicrm_api3('CustomField', 'getsingle', ['id' => $id]);
      $this->assertEquals(1, $updatedField['is_active']);
    }

    // check custom group was enabled
    $updatedGroup = civicrm_api3('CustomGroup', 'getsingle', ['id' => $groupId]);
    $this->assertEquals(1, $updatedGroup['is_active']);
  }

  public function testNonExistingGroupNameWillThrowException() {
    $groupName = 'Lala';
    $expectedMessage = 'Could not find group with name "Lala"';
    $this->setExpectedException(\Exception::class, $expectedMessage);
    $switcher = new CustomGroupStatusSwitcher();
    $switcher->enable($groupName);
  }

  /**
   * CiviCRM test transactions are broken by custom group creation. If you
   * create and subsequently delete a custom group in a test it will drop the
   * table but leave the civicrm_custom_group entry. This means the next
   * time the test is run it will fail when it tries to drop a table that
   * doesn't exist.
   *
   * Similarly with custom fields, if you drop one inside a test the column
   * will be dropped outside of the transaction, but the field will only be
   * dropped inside the transaction, meaning the next time you try to delete
   * a custom group it will fail because it tries to drop columns that don't
   * exist.
   *
   * To avoid these problems all custom group / field creation and deletion is
   * done outside the test. The CiviTestListener manages transactions in
   * SetUp and TearDown, so we use the "BeforeClass" and "AfterClass" methods
   */
  public static function setUpBeforeClass() {
    // If the last test critically errored the group might still exist
    self::deleteCustomGroupIfExists('Foo');

    self::$customGroup = CustomGroupFabricator::fabricate(['name' => 'Foo']);
    self::createCustomFields(2);
  }

  /**
   * Clean up everything created in this test class
   */
  public static function tearDownAfterClass() {
    static::deleteCustomGroupIfExists(self::$customGroup['name']);
  }

  /**
   * Checks if a custom group exists (by name) and deletes it if it does
   *
   * @param string $groupName
   */
  private static function deleteCustomGroupIfExists($groupName) {
    $existing = civicrm_api3('CustomGroup', 'get', ['name' => $groupName]);
    if ($existing['count'] > 0) {
      $existing = array_shift($existing['values']);
      civicrm_api3('CustomGroup', 'delete', ['id' => $existing['id']]);
    }
  }

  /**
   * Create some custom fields for the test custom group
   *
   * @param int $count
   */
  private static function createCustomFields($count) {
    $customFields = [];

    for ($i = 0; $i < $count; $i++) {
      $params['custom_group_id'] = self::$customGroup['id'];
      $params['name'] = 'bar_' . $i;
      $customFields[] = CustomFieldFabricator::fabricate($params);
    }

    self::$customFields = $customFields;
  }

}
