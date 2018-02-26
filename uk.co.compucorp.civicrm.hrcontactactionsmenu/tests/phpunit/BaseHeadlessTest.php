<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

abstract class BaseHeadlessTest extends PHPUnit_Framework_TestCase implements
  HeadlessInterface, TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->installMe(__DIR__)
      ->apply();
  }
}
