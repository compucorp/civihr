<?php

use CRM_HRCore_CMSData_UserAccount_Drupal as DrupalUserAccount;

/**
 * Class CRM_HRCore_CMSData_UserAccount_DrupalTest
 *
 * @group headless
 */
class CRM_HRCore_CMSData_UserAccount_DrupalTest extends CRM_HRCore_Test_BaseHeadlessTest {

  public function testCancelUserAccount() {
    $contactData = ['cmsId' => 1];
    $userAccount = new DrupalUserAccount();
    $result = $userAccount->cancel($contactData);
    $this->assertEquals($contactData['cmsId'], $result['uid']);
    $this->assertEquals('user_cancel_reassign', $result['method']);
    $this->assertEquals(FALSE, $result['params']['user_cancel_notify']);
    $this->assertEquals('user_cancel_reassign', $result['params']['user_cancel_method']);
  }

  public function testDisableUserAccount() {
    $contactData = ['cmsId' => 1];
    $userAccount = new DrupalUserAccount();
    $user = $userAccount->disable($contactData);

    $this->assertEquals(0, $user->status);
  }

  public function testEnableUserAccount() {
    $contactData = ['cmsId' => 1];
    $userAccount = new DrupalUserAccount();
    $user = $userAccount->enable($contactData);

    $this->assertEquals(1, $user->status);
  }
}
