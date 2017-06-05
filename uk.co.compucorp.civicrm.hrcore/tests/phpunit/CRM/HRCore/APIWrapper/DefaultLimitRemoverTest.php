<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class CRM_HRCore_APIWrapper_DefaultLimitRemoverTest
 *
 * @group headless
 */
class CRM_HRCore_APIWrapper_DefaultLimitRemoverTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  private $defaultLimitRemoverObj;

  private $testParameters = [];

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    $this->defaultLimitRemoverObj = new CRM_HRCore_APIWrapper_DefaultLimitRemover();

    $this->testParameters['params'] = [
      'action' => 'get',
      'entity' => 'contact',
      'id' => 1,
    ];
  }

  public function testFromApiInputWithLimitSetInParameters() {
    $this->testParameters['params']['options']['limit'] = 10;

    $actualValue =  $this->defaultLimitRemoverObj->fromApiInput($this->testParameters);

    $this->assertEquals(
      $this->testParameters['params']['options']['limit'],
      $actualValue['params']['options']['limit']
    );
  }

  public function testFromApiInputWithLimitNotSetInParameters() {
    $actualValue =  $this->defaultLimitRemoverObj->fromApiInput($this->testParameters);

    $this->assertEquals(
      $this->defaultLimitRemoverObj->getDefaultNoLimitValue(),
      $actualValue['params']['options']['limit']
    );
  }

  public function testToApiInputDoesNotChangeResults() {
    $testResult = [
      'is_error' => 0,
      'version' => 3,
      'count' =>  5
    ];

    $actualValue =  $this->defaultLimitRemoverObj->toApiOutput(
      $this->testParameters,
      $testResult
    );

    $this->assertEquals($testResult, $actualValue);
  }

}
