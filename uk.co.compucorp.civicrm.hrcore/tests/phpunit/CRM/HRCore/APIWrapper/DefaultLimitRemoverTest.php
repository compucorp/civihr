<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

use CRM_HRCore_APIWrapper_DefaultLimitRemover as DefaultLimitRemover;

/**
 * Class CRM_HRCore_APIWrapper_DefaultLimitRemoverTest
 *
 * @group headless
 */
class CRM_HRCore_APIWrapper_DefaultLimitRemoverTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  /**
   * @var DefaultLimitRemover
   */
  private $defaultLimitRemover;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function setUp() {
    $this->defaultLimitRemover = new DefaultLimitRemover();
  }

  public function testTheDefaultLimitShouldNotBeAppliedIfASpecificLimitHasBeenSet() {
    $testParameters['params']['options']['limit'] = 10;

    $actualValue =  $this->defaultLimitRemover->fromApiInput($testParameters);

    $this->assertEquals(
      $testParameters['params']['options']['limit'],
      $actualValue['params']['options']['limit']
    );
  }

  public function testTheDefaultLimitValueShouldBeAppliedIfNoLimitHasBeenSet() {
    $actualValue =  $this->defaultLimitRemover->fromApiInput([]);

    $this->assertEquals(
      $this->defaultLimitRemover->getDefaultNoLimitValue(),
      $actualValue['params']['options']['limit']
    );
  }

  public function testTheResultOfAPIResponseShouldNotBeAffectedOrChanged() {
    $testResult = [
      'is_error' => 0,
      'version' => 3,
      'count' =>  5
    ];

    $actualValue =  $this->defaultLimitRemover->toApiOutput([], $testResult);

    $this->assertEquals($testResult, $actualValue);
  }

}
