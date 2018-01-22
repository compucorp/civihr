<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

abstract class CRM_HRCore_Test_BaseHeadlessTest extends PHPUnit_Framework_TestCase
  implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    $requiredExtensions = [
      'uk.co.compucorp.civicrm.tasksassignments',
      'org.civicrm.hrrecruitment',
      'uk.co.compucorp.civicrm.hrleaveandabsences',
      'org.civicrm.hrjobcontract', // L&A depends on HRJobContract
    ];

    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->install($requiredExtensions)
      ->apply();
  }

}
