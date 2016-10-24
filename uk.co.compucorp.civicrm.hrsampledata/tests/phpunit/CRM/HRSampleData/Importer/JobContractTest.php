<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/JobContract.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRHoursLocation as HoursLocationFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRPayScale as PayScaleFabricator;

/**
 * Class CRM_HRSampleData_Importer_JobContractTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_JobContractTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  private $testContact;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->install('org.civicrm.hrjobcontract')
      ->install('org.civicrm.hrabsence')
      ->apply();
  }

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate(['first_name' => 'chrollo', 'last_name' => 'lucilfer']);

    HoursLocationFabricator::fabricate();
    PayScaleFabricator::fabricate();

    $contractOptionValues = [
      'hrjc_contract_type',
      'hrjc_contract_end_reason',
      'hrjc_location',
      'hrjc_pension_type',
    ];
    foreach($contractOptionValues as $group) {
      OptionValueFabricator::fabricate($group);
    }
  }

  public function testImport() {

    $this->rows[] = [
      25,
      $this->testContact['id'],
      'Subject Head - Literacy',
      'Subject Head - Literacy',
      'Donor funded position',
      'test option',
      '2012-04-28',
      '2016-07-28',
      'test option',
      3,
      'Month',
      3,
      'Month',
      'test option',
      $this->testContact['id'],
      'Individual',
      'GP helpline, NHS cashback & up to 75% no claims discount',
      'No',
      $this->testContact['id'],
      'Individual',
      'Maximum term 40 years or 75 years of age',
      'No',
      'test location',
      'Part_Time',
      35,
      'Week',
      0.92,
      35,
      38,
      'test scale',
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
      'test option',
      'Sick:0,Vacation:28,Maternity:0,Paternity:0,TOIL:0,Other:0',
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runImporter('CRM_HRSampleData_Importer_JobContract', $this->rows, $mapping);


    $this->assertNotEmpty($contract = $this->apiQuickGet('HRJobContract',null, null, ['contact_id' => $this->testContact['id']]));

    $contractID = $contract['id'];
    $this->assertNotEmpty($revision = $this->apiQuickGet('HRJobContractRevision',null ,null, ['jobcontract_id' => $contractID]));

    $revisionID = $revision['id'];
    $entities = ['HRJobDetails', 'HRJobHealth', 'HRJobHour', 'HRJobPay', 'HRJobPension', 'HRJobLeave'];
    foreach ($entities as $entity) {
      $this->assertEquals($revisionID, $this->apiQuickGet($entity,'jobcontract_revision_id', $revisionID));
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
