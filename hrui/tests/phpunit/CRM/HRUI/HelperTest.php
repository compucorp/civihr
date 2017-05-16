<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRUI_HelperTest
 *
 * @group headless
 */
class CRM_HRUI_HelperTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  use HRUITrait;

  public function setUpHeadless() {
    // hrcase create ( Line Manager is ) relationship type which is need for the tests
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->install('org.civicrm.hrident')
      ->install('uk.co.compucorp.civicrm.tasksassignments')
      ->installMe(__DIR__)
      ->install('org.civicrm.hrcase')
      ->apply();
  }

  public function testGetLineManagersList() {
    $contactParamsA = array("first_name" => "chrollo", "last_name" => "lucilfer");
    $contactParamsB = array("first_name" => "hisoka", "last_name" => "morou");
    $contactA = $this->createContact($contactParamsA);
    $contactB = $this->createContact($contactParamsB)
    ;
    $this->createRelationship($contactA, $contactB, 'Line Manager is');

    $managers = CRM_HRUI_Helper::getLineManagersList($contactA);
    $this->assertContains('hisoka morou', $managers);
    $this->assertEquals(1, count($managers));

    // add another line manager
    $contactParamsC = array("first_name" => "illumi", "last_name" => "zoldyck");
    $contactC = $this->createContact($contactParamsC);

    $this->createRelationship($contactA, $contactC, 'Line Manager is');

    $managers = CRM_HRUI_Helper::getLineManagersList($contactA);
    $this->assertContains('illumi zoldyck', $managers);
    $this->assertContains('hisoka morou', $managers);
    $this->assertCount(2, $managers);
  }

}
