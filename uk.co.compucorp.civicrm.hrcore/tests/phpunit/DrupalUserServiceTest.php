<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use Civi\Test;
use CRM_HRCore_Service_DrupalUserService as DrupalUserService;
use CRM_HRCore_Service_DrupalRoleService as DrupalRoleService;

/**
 * @group headless
 */
class DrupalUserServiceTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  /**
   * @var string
   */
  private $testEmail = 'foo@bar.com';

  /**
   * @var array
   */
  private $testContact;

  public function setUpHeadless() {
    return Test::headless()->installMe(__DIR__)->apply();
  }

  public function setUp() {
    $this->cleanup();
    $params = ['email' => $this->testEmail];
    $this->testContact = CRM_HRCore_Test_Fabricator_Contact::fabricate($params);
    $this->registerCurrentLoggedInContactInSession($this->testContact['id']);
  }

  public function tearDown() {
    $this->cleanup();
  }

  public function testBasicUserCreate() {
    $roleService = $this->prophesize(DrupalRoleService::class);
    $drupalUserService = new DrupalUserService($roleService->reveal());
    $user = $drupalUserService->createNew(
      $this->testContact['id'],
      $this->testEmail
    );

    $this->assertEquals(0, $user->status);
    $this->assertEquals($this->testEmail, $user->mail);
  }

  public function testCreateActiveWithRoles() {
    $roles = ['testrole'];
    $mockRids = [8 => '8'];
    $roleService = $this->prophesize(DrupalRoleService::class);
    $roleService->getRoleIds($roles)->willReturn($mockRids);
    $drupalUserService = new DrupalUserService($roleService->reveal());
    $user = $drupalUserService->createNew(
      $this->testContact['id'],
      $this->testEmail,
      TRUE,
      $roles
    );

    $this->assertEquals(1, $user->status);
    $this->assertEquals($this->testEmail, $user->mail);
    $this->assertArrayHasKey(8, $user->roles);
  }

  protected function cleanup() {
    $user = user_load_by_mail($this->testEmail);
    if ($user) {
      user_delete($user->uid);
    }
  }

  /**
   * @param $contactID
   */
  private function registerCurrentLoggedInContactInSession($contactID) {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);
  }

}
