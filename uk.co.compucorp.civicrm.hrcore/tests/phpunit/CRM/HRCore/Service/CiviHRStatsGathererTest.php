<?php

use CRM_HRCore_Service_CiviHRStatsGatherer as CiviHRStatsGatherer;
use CRM_HRCore_CMSData_Variable_VariableServiceInterface as VariableServiceInterface;
use Civi\Test\TransactionalInterface;
use Civi\Test\HeadlessInterface;

/**
 * @group headless
 */
class CiviHRStatsGathererTest extends PHPUnit_Framework_TestCase
  implements TransactionalInterface, HeadlessInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testGatheringStats() {
    $variableService = $this->prophesize(VariableServiceInterface::class);
    $variableService->get('site_name')->willReturn('foo');
    $gatherer = new CiviHRStatsGatherer($variableService->reveal());
    $stats = $gatherer->gather();
    $today = new \DateTime();
    $comparisonFormat = 'Y-m-d';
    $todayFmt = $today->format($comparisonFormat);
    $generatedFmt = $stats->getGenerationDate()->format($comparisonFormat);

    $this->assertNotNull($stats->getSiteUrl());
    $this->assertEquals('foo', $stats->getSiteName());
    $this->assertEquals($todayFmt, $generatedFmt);
  }

}
