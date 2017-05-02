<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;
use Civi\Test\CiviEnvBuilder;

/**
 * Class CRM_HRUI_HelperTest
 *
 * @group headless
 */
class CRM_HRUI_HelperTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  use HRUITrait;

  protected $requiredExtensions = [
    'uk.co.compucorp.civicrm.tasksassignments',
    'org.civicrm.hrcase'
  ];

  /**
   * @return CiviEnvBuilder
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->install('org.civicrm.hrident')
      ->install('uk.co.compucorp.civicrm.tasksassignments')
      ->installMe(__DIR__)
      ->install($this->requiredExtensions)
      ->apply();
  }

  /**
   * Checks that the CRM_HRUI_Helper returns all line managers
   */
  public function testGetLineManagersList() {
    $relationshipType = 'Line Manager is';
    $relationshipTypeInverse = 'Line Manager';
    $this->createRelationshipType($relationshipType, $relationshipTypeInverse);

    $contactA = $this->createContact('chrollo', 'lucilfer');
    $contactB = $this->createContact('hisoka', 'morou');

    $this->createRelationship($contactA, $contactB, $relationshipType);

    $managers = CRM_HRUI_Helper::getLineManagersList($contactA);
    $this->assertContains('hisoka morou', $managers);
    $this->assertEquals(1, count($managers));

    // add another line manager
    $contactC = $this->createContact('illumi', 'zoldyck');

    $this->createRelationship($contactA, $contactC, $relationshipType);

    $managers = CRM_HRUI_Helper::getLineManagersList($contactA);
    $this->assertContains('illumi zoldyck', $managers);
    $this->assertContains('hisoka morou', $managers);
    $this->assertCount(2, $managers);
  }

}
