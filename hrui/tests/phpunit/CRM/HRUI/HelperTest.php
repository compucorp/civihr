<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use Civi\Test\CiviEnvBuilder;
use CRM_HRCore_Test_Fabricator_Contact as ContactMaker;

/**
 * Class CRM_HRUI_HelperTest
 *
 * @group headless
 */
class CRM_HRUI_HelperTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  use HRUITrait;

  protected $requiredExtensions = [
    'uk.co.compucorp.civicrm.hrcore', // required for fabricator
    'org.civicrm.hrident', // creates the Identity custom group required by hrcase
    'uk.co.compucorp.civicrm.tasksassignments', // if not enabled will try to redirect
    'org.civicrm.hrcase' // creates the line manager relationship type
  ];

  /**
   * @return CiviEnvBuilder
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install($this->requiredExtensions)
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * Checks that the CRM_HRUI_Helper returns all line managers
   */
  public function testGetLineManagersList() {
    $relationshipType = 'Line Manager is';

    $contactA = $this->createContact('chrollo', 'lucilfer');
    $contactB = $this->createContact('hisoka', 'morou');
    $contactC = $this->createContact('illumi', 'zoldyck');

    $this->createRelationship($contactA, $contactB, $relationshipType);
    $managers = CRM_HRUI_Helper::getLineManagersList($contactA);

    $this->assertContains('hisoka morou', $managers);
    $this->assertEquals(1, count($managers));

    $this->createRelationship($contactA, $contactC, $relationshipType);
    $managers = CRM_HRUI_Helper::getLineManagersList($contactA);

    $this->assertContains('illumi zoldyck', $managers);
    $this->assertContains('hisoka morou', $managers);
    $this->assertCount(2, $managers);
  }

  /**
   * @param string $firstName
   * @param string $lastName
   *
   * @return int
   */
  private function createContact($firstName, $lastName) {
    return ContactMaker::fabricate([
      'first_name' => $firstName,
      'last_name' => $lastName
    ])['id'];
  }

}
