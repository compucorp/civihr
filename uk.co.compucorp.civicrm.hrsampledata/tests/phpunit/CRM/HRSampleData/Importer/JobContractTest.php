<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRHoursLocation as HoursLocationFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRPayScale as PayScaleFabricator;

/**
 * Class CRM_HRSampleData_Importer_JobContractTest
 *
 * @group headless
 */
class CRM_HRSampleData_CSVProcessor_JobContractTest extends CRM_HRSampleData_BaseCSVProcessorTest {

  private $testContact;

  private $testLocationType;

  private $testPayScale;

  private $optionValues = [];

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate();

    $this->testLocationType = HoursLocationFabricator::fabricate();

    $this->testPayScale = PayScaleFabricator::fabricate();

    $contractOptionValues = [
      'hrjc_contract_type',
      'hrjc_contract_end_reason',
      'hrjc_location',
      'hrjc_pension_type',
    ];
    foreach($contractOptionValues as $group) {
      $this->optionValues[$group] = OptionValueFabricator::fabricate(['option_group_id' => $group]);
    }
  }

  public function testProcess() {

    $this->rows[] = [
      25,
      $this->testContact['id'],
      'Subject Head - Literacy',
      'Subject Head - Literacy',
      'Donor funded position',
      $this->optionValues['hrjc_contract_type']['name'],
      '2012-04-28',
      '2016-07-28',
      $this->optionValues['hrjc_contract_end_reason']['name'],
      3,
      'Month',
      3,
      'Month',
      $this->optionValues['hrjc_location']['name'],
      $this->testContact['id'],
      'Individual',
      'GP helpline, NHS cashback & up to 75% no claims discount',
      'No',
      $this->testContact['id'],
      'Individual',
      'Maximum term 40 years or 75 years of age',
      'No',
      $this->testLocationType['location'],
      'Part_Time',
      35,
      'Week',
      0.92,
      35,
      38,
      $this->testPayScale['pay_scale'],
      'Paid',
      53513,
      'Year',
      'USD',
      '53513',
      1,
      '[{""name"":""1"",""type"":""1"",""amount_pct"":"""",""amount_abs"":""600.00""}]',
      'Monthly',
      4459.42,
      4509.42,
      1,
      5,
      5,
      $this->optionValues['hrjc_pension_type']['name'],
      'Sick:0,Annual_Leave:28,Maternity:0,Paternity:0,TOIL:0,Other:0',
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runProcessor('CRM_HRSampleData_Importer_JobContract', $this->rows, $mapping);

    $contract = $this->apiGet('HRJobContract', ['contact_id' => $this->testContact['id']]);
    $this->assertEquals($this->testContact['id'], $contract['contact_id']);

    $contractID = $contract['id'];

    $revision = $this->apiGet('HRJobContractRevision', ['jobcontract_id' => $contractID]);
    $this->assertEquals($contractID, $revision['jobcontract_id']);

    $revisionID = $revision['id'];
    $entities = ['HRJobDetails', 'HRJobHealth', 'HRJobHour', 'HRJobPay', 'HRJobPension', 'HRJobLeave'];
    foreach ($entities as $entity) {
      $entityList[$entity] = $this->apiGet($entity,['jobcontract_revision_id' => $revisionID]);
      $this->assertEquals($revisionID, $entityList[$entity]['jobcontract_revision_id']);
    }
  }

  private function importHeadersFixture() {
    return [
      'HRJobContract-id',
      'HRJobContract-contact_id',
      'HRJobDetails-position',
      'HRJobDetails-title',
      'HRJobDetails-funding_notes',
      'HRJobDetails-contract_type',
      'HRJobDetails-period_start_date',
      'HRJobDetails-period_end_date',
      'HRJobDetails-end_reason',
      'HRJobDetails-notice_amount',
      'HRJobDetails-notice_unit',
      'HRJobDetails-notice_amount_employee',
      'HRJobDetails-notice_unit_employee',
      'HRJobDetails-location',
      'HRJobHealth-provider',
      'HRJobHealth-plan_type',
      'HRJobHealth-description',
      'HRJobHealth-dependents',
      'HRJobHealth-provider_life_insurance',
      'HRJobHealth-plan_type_life_insurance',
      'HRJobHealth-description_life_insurance',
      'HRJobHealth-dependents_life_insurance',
      'HRJobHour-location_standard_hours',
      'HRJobHour-hours_type',
      'HRJobHour-hours_amount',
      'HRJobHour-hours_unit',
      'HRJobHour-hours_fte',
      'HRJobHour-fte_num',
      'HRJobHour-fte_denom',
      'HRJobPay-pay_scale',
      'HRJobPay-is_paid',
      'HRJobPay-pay_amount',
      'HRJobPay-pay_unit',
      'HRJobPay-pay_currency',
      'HRJobPay-pay_annualized_est',
      'HRJobPay-pay_is_auto_est',
      'HRJobPay-annual_benefits',
      'HRJobPay-pay_cycle',
      'HRJobPay-pay_per_cycle_gross',
      'HRJobPay-pay_per_cycle_net',
      'HRJobPension-is_enrolled',
      'HRJobPension-ee_contrib_pct',
      'HRJobPension-er_contrib_pct',
      'HRJobPension-pension_type',
      'HRJobLeave-leave_amount',
    ];
  }

}
