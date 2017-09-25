<?php

use CRM_Hrjobcontract_Test_Fabricator_HRPayScale as PayScaleFabricator;

/**
 * Class CRM_HRSampleData_Cleaner_HRPayScaleTest
 *
 * @group headless
 */
class CRM_HRSampleData_Cleaner_HRPayScaleTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testProcessWithDeleteOnUninstallOn() {
    $testPayScale = PayScaleFabricator::fabricate();

    $this->rows[] = [
      $testPayScale['pay_scale'],
      'USD',
      '35000',
      'Year',
      1,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_HRPayScale', $this->rows);

    $payScale = $this->apiGet('HRPayScale', ['pay_scale' => $testPayScale['pay_scale']]);
    $this->assertEmpty($payScale);
  }

  public function testProcessWithDeleteOnUninstallOff() {
    $testPayScale = PayScaleFabricator::fabricate();

    $this->rows[] = [
      $testPayScale['pay_scale'],
      'USD',
      '35000',
      'Year',
      0,
    ];

    $this->runProcessor('CRM_HRSampleData_Cleaner_HRPayScale', $this->rows);

    $payScale = $this->apiGet('HRPayScale', ['pay_scale' => $testPayScale['pay_scale']]);
    $this->assertEquals($testPayScale['pay_scale'], $payScale['pay_scale']);
  }

  private function importHeadersFixture() {
    return [
      'pay_scale',
      'currency',
      'amount',
      'pay_frequency',
      'delete_on_uninstall',
    ];
  }
}
