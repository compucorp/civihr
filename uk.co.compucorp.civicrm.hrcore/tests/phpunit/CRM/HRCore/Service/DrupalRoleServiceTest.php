<?php

use CRM_HRCore_Service_DrupalRoleService as DrupalRoleService;

/**
 * @group headless
 */
class DrupalRoleServiceTest extends CRM_HRCore_Test_BaseHeadlessTest {

  /**
   * @var string
   */
  protected $roleName = 'Fake Role';

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
