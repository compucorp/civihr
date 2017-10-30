<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

abstract class CRM_Hrjobcontract_Test_BaseHeadlessTest extends PHPUnit_Framework_TestCase
  implements HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
               ->install('uk.co.compucorp.civicrm.hrcore')
               ->install('uk.co.compucorp.civicrm.hrleaveandabsences')
               ->installMe(__DIR__)
               ->apply();
  }

}
