<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use Civi\Test;
use CRM_HRCore_Service_DrupalRoleService as DrupalRoleService;

/**
 * @group headless
 */
class DrupalRoleServiceTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  /**
   * @var string
   */
  protected $roleName = 'Fake Role';

  public function setUpHeadless() {
    return Test::headless()->installMe(__DIR__)->apply();
  }

  public function tearDown() {
    user_role_delete($this->roleName);
  }

  public function testBasicRoleIdFetching() {
    $roleName = 'Fake Role';
    $roleService = new DrupalRoleService();
    $fakeRole = new \stdClass();
    $fakeRole->name = $roleName;
    user_role_save($fakeRole);

    $roleIds = $roleService->getRoleIds([$roleName]);

    $this->assertCount(1, $roleIds);
    $this->assertEquals($fakeRole->rid, array_shift($roleIds));
  }

}
