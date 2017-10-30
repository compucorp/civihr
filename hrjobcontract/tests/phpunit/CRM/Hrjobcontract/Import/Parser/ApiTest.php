<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRCore_Test_Fabricator_OptionValue as OptionValueFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRHoursLocation as HRHoursLocationFabicator;
use CRM_Hrjobcontract_Test_Fabricator_HRPayScale as HRPayScaleFabricator;

/**
 * Class CRM_Hrjobcontract_Import_Parser_ApiTest
 *
 * @group headless
 */
class CRM_Hrjobcontract_Import_Parser_ApiTest extends CRM_Hrjobcontract_Test_BaseHeadlessTest {

  public $_contractTypeID;
  private $_defaultImportData = [];
  private $_insurancePlanTypes = [];
  private $_hoursLocation = [];
  private $_payScale = [];

  public function setUp() {
    $session = CRM_Core_Session::singleton();
    $session->set('dateTypes', 1);

    $this->_contractTypeID = $this->createTestContractType();
    $this->createInsurancePlanTypes();
    $this->createHoursLocation();
    $this->createPayScale();

    $this->_defaultImportData = [
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobDetails-location' => 'headquarters',
      'HRJobDetails-period_end_date' => '2016-01-20',
      'HRJobDetails-end_reason' => 'Planned',
      'HRJobDetails-notice_amount_employee' => '3',
      'HRJobDetails-notice_unit_employee' => 'Week',
      'HRJobDetails-notice_amount' => '4',
      'HRJobDetails-notice_unit' => 'day',
      'HRJobDetails-funding_notes' => 'sample',
    ];
  }

  public function createPayScale() {
    $this->_payScale = HRPayScaleFabricator::fabricate();
    $this->_payScale['importLabel'] = $this->_payScale['pay_scale'] . ' - '
      . $this->_payScale['currency'] . ' '
      . $this->_payScale['amount'] . ' per ' . $this->_payScale['pay_frequency']
    ;
  }

  public function createHoursLocation() {
    $this->_hoursLocation = HRHoursLocationFabicator::fabricate(['standard_hours' => '36.00']);
    $this->_hoursLocation['importLabel'] = $this->_hoursLocation['location']
      . ' - ' . $this->_hoursLocation['standard_hours']
      .  ' hours per ' . $this->_hoursLocation['periodicity'];
  }

  public function testMandatoryFieldsImportWithContactID() {
    $contact2Params = array(
      'first_name' => 'John_1',
      'last_name' => 'Snow_1',
      'email' => 'a1@b1.com',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-contact_id' => $contactID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID);
  }

  public function testMandatoryFieldsImportWithContactExternalIdentifier() {
    $contact2Params = array(
      'first_name' => 'John_2',
      'last_name' => 'Snow_2',
      'email' => 'a2@b2.com',
      'external_identifier' => 'abcdefg12345',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-external_identifier' => $contact2Params['external_identifier'],
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID);
  }

  public function testMandatoryFieldsImportWithContactEmail() {
    $contact2Params = array(
      'first_name' => 'John_3',
      'last_name' => 'Snow_3',
      'email' => 'a3@b3.com',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-email' => $contact2Params['email'],
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID);
  }

  public function testJobDetailsImport() {
    $contact2Params = array(
      'first_name' => 'John_4',
      'last_name' => 'Snow_4',
      'email' => 'a4@b4.com',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-contact_id' => $contactID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobDetails-location' => 'headquarters',
      'HRJobDetails-period_end_date' => '2016-01-20',
      'HRJobDetails-end_reason' => 'Planned',
      'HRJobDetails-notice_amount_employee' => '3',
      'HRJobDetails-notice_unit_employee' => 'Week',
      'HRJobDetails-notice_amount' => '4',
      'HRJobDetails-notice_unit' => 'day',
      'HRJobDetails-funding_notes' => 'sample',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID);
  }

  public function testHourImport() {
    $contact2Params = array(
      'first_name' => 'John_5',
      'last_name' => 'Snow_5',
      'email' => 'a5@b5.com',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-contact_id' => $contactID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobHour-location_standard_hours' => $this->_hoursLocation['importLabel'],
      'HRJobHour-hours_type' => 'part time',
      'HRJobHour-hours_amount' => '25',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID, 'HRJobHour');
  }

  public function testHourAutoPopulatedFields() {
    $contact2Params = array(
      'first_name' => 'John_55',
      'last_name' => 'Snow_55',
      'email' => 'a55@b55.com',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-contact_id' => $contactID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobHour-location_standard_hours' => $this->_hoursLocation['importLabel'],
      'HRJobHour-hours_type' => 'part time',
      'HRJobHour-hours_amount' => '25.52',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $expected = array('hours_unit'=>'Week', 'fte_num'=> 319, 'fte_denom'=>450, 'hours_fte'=>0.71);
    $this->validateHourAutoFields($contactID, $expected);
  }

  public function testFTEFieldsAreSetToZeroWhenImportingCasualHoursType() {
    $contact1 = ContactFabricator::fabricate();
    $casualContract = $this->buildContractInfo([
      'HRJobHour-location_standard_hours' => $this->_hoursLocation['importLabel'],
      'HRJobContract-contact_id' => $contact1['id'],
      'HRJobHour-hours_type' => 'Casual',
      'HRJobHour-hours_amount' => '16'
    ]);
    $importCasualResponse = $this->runImport($casualContract);
    $this->assertEquals(CRM_Import_Parser::VALID, $importCasualResponse);

    $expected = ['fte_num' => 0, 'fte_denom' => 0, 'hours_fte' => 0];
    $this->validateHourAutoFields($contact1['id'], $expected);
  }

  public function testFTEFieldsAreSetToZeroWhenImportingEmptyHoursAmount() {
    $contact2 = ContactFabricator::fabricate();
    $emptyHoursContract = $this->buildContractInfo([
      'HRJobHour-location_standard_hours' => $this->_hoursLocation['importLabel'],
      'HRJobContract-contact_id' => $contact2['id'],
      'HRJobHour-hours_type' => 'part time',
      'HRJobHour-hours_amount' => ''
    ]);
    $importEmptyContractResponse = $this->runImport($emptyHoursContract);
    $this->assertEquals(CRM_Import_Parser::VALID, $importEmptyContractResponse);

    $expected = ['fte_num' => 0, 'fte_denom' => 0, 'hours_fte' => 0];
    $this->validateHourAutoFields($contact2['id'], $expected);
  }

  public function testFTEFieldsAreSetToZeroWhenImportingOnlyMandatoryFields() {
    $contact3 = ContactFabricator::fabricate();
    $importResponse = $this->runImport([
      'HRJobContract-contact_id' => $contact3['id'],
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-period_start_date' => '2016-01-01'
    ]);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $expected = ['fte_num' => 0, 'fte_denom' => 0, 'hours_fte' => 0];
    $this->validateHourAutoFields($contact3['id'], $expected);
  }

  public function testActualHoursIsDeducedForFullTimeHoursType() {
    $contact1 = ContactFabricator::fabricate();
    $contract = $this->buildContractInfo([
      'HRJobHour-location_standard_hours' => $this->_hoursLocation['importLabel'],
      'HRJobContract-contact_id' => $contact1['id'],
      'HRJobHour-hours_type' => 'Full Time',
      'HRJobHour-hours_amount' => '16'
    ]);

    $importResponse = $this->runImport($contract);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $expected = [
      'fte_num' => 1,
      'fte_denom' => 1,
      'hours_fte' => 1,
      'hours_amount' => $this->_hoursLocation['standard_hours']
    ];
    $this->validateHourAutoFields($contact1['id'], $expected);
  }

  /**
   * Merges parmeter array given with default import data for contracts. Given
   * parameters override values of default import data array.
   *
   * @param array $params
   *   Parameters to be set to array
   *
   * @return array
   *   Result of merging given array with default import data array
   */
  private function buildContractInfo($params) {
    return array_merge($this->_defaultImportData, $params);
  }

  public function testPayImport() {
    $contact2Params = array(
      'first_name' => 'John_6',
      'last_name' => 'Snow_6',
      'email' => 'a6@b6.com',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    // TODO : add deduction and benefit amounts to params
    $params = array(
      'HRJobContract-contact_id' => $contactID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobPay-is_paid' => 'Yes',
      'HRJobPay-pay_scale' => $this->_payScale['importLabel'],
      'HRJobPay-pay_currency' => 'USD',
      'HRJobPay-pay_amount' => '35000',
      'HRJobPay-pay_unit' => 'year',
      'HRJobPay-pay_cycle' => 'Monthly',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID, 'HRJobPay');
  }

  public function testPayAutoPopulatedFields() {
    $contact2Params = array(
      'first_name' => 'John_66',
      'last_name' => 'Snow_66',
      'email' => 'a66@b66.com',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-contact_id' => $contactID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobPay-is_paid' => 'Yes',
      'HRJobPay-pay_scale' => $this->_payScale['importLabel'],
      'HRJobPay-pay_currency' => 'USD',
      'HRJobPay-pay_amount' => '35000',
      'HRJobPay-pay_unit' => 'year',
      'HRJobPay-pay_cycle' => 'Monthly',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $expected = array('pay_annualized_est'=>35000, 'pay_per_cycle_gross'=> 2916.67, 'pay_per_cycle_net'=>2916.67);
    $this->validatePayAutoFields($contactID, $expected);
  }

  public function testInsuranceImport() {
    $contact2Params = array(
      'first_name' => 'John_8',
      'last_name' => 'Snow_8',
      'email' => 'a8@b8.com',
      'contact_type' => 'Individual',
    );

    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-contact_id' => $contactID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobHealth-provider' => $this->createProvider('Health Provider', 'Health_Insurance_Provider'),
      'HRJobHealth-dependents' => 'HI Description',
      'HRJobHealth-description' => 'HI dependents',
      'HRJobHealth-plan_type' => $this->_insurancePlanTypes[0]['label'],
      'HRJobHealth-provider_life_insurance' => $this->createProvider('Life Provider', 'Life_Insurance_Provider'),
      'HRJobHealth-dependents_life_insurance' => 'LI dependents',
      'HRJobHealth-description_life_insurance' => 'LI description',
      'HRJobHealth-plan_type_life_insurance' => $this->_insurancePlanTypes[1]['label'],
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID, 'HRJobHealth');
  }

  public function testWrongInsurancePlanTypeImport() {
    $contact2Params = array(
      'first_name' => 'John_8',
      'last_name' => 'Snow_8',
      'email' => 'a8@b8.com',
      'contact_type' => 'Individual',
    );

    $contactID = $this->createTestContact($contact2Params);
    $params = [
      'HRJobContract-contact_id' => $contactID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobHealth-dependents' => 'HI Description',
      'HRJobHealth-description' => 'HI dependents',
      'HRJobHealth-plan_type' => 'Wrong Plan Type Label',
      'HRJobHealth-dependents_life_insurance' => 'LI dependents',
      'HRJobHealth-description_life_insurance' => 'LI description',
      'HRJobHealth-plan_type_life_insurance' => $this->_insurancePlanTypes[1]['label'],
    ];

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testPensionImport() {
    $contact2Params = array(
      'first_name' => 'John_9',
      'last_name' => 'Snow_9',
      'email' => 'a9@b9.com',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-contact_id' => $contactID,
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobPension-is_enrolled' => 'Yes',
      'HRJobPension-er_cohntrib_pct' => '10',
      'HRJobPension-ee_contrib_pct' => '12',
      'HRJobPension-ee_contrib_abs' => '4000',
      'HRJobPension-ee_evidence_note' => 'sample',
      'HRJobPension-pension_type' => $this->createProvider('Pension Provider Contact', 'Pension_Provider'),
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID, 'HRJobPension');

    $params['HRJobPension-pension_type'] = 'Wrong Pension Provider';
    $failedImportResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::ERROR, $failedImportResponse);
  }

  private function createProvider($providerName, $type) {
    $provider = ContactFabricator::fabricateOrganization([
      'contact_type' => 'Organization',
      'contact_sub_type' => $type,
      'organization_name' => $providerName,
    ]);

    return $provider['id'];
  }

  public function testImportingContractWithEndDateWithoutEndReason() {
    $contact2Params = array(
      'first_name' => 'John_54',
      'last_name' => 'Snow_54',
      'email' => '54@b54.com',
      'contact_type' => 'Individual',
    );
    $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-email' => $contact2Params['email'],
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobDetails-period_end_date' => '2016-01-20',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testImportingContractWithEndDateAndEndReason() {
    $contact2Params = array(
      'first_name' => 'John_54',
      'last_name' => 'Snow_54',
      'email' => '54@b54.com',
      'contact_type' => 'Individual',
    );
    $contactID = $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-email' => $contact2Params['email'],
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobDetails-period_end_date' => '2016-01-20',
      'HRJobDetails-end_reason' => 'Planned'
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID);
  }

  public function testImportingContractWithEndDateAndInvalidEndReason() {
    $contact2Params = array(
      'first_name' => 'John_54',
      'last_name' => 'Snow_54',
      'email' => '54@b54.com',
      'contact_type' => 'Individual',
    );
    $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-email' => $contact2Params['email'],
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobDetails-end_reason' => 'Planned'
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testImportingContractWithoutEndDateWithEndReason() {
    $contact2Params = array(
      'first_name' => 'John_54',
      'last_name' => 'Snow_54',
      'email' => '54@b54.com',
      'contact_type' => 'Individual',
    );
    $this->createTestContact($contact2Params);
    $params = array(
      'HRJobContract-email' => $contact2Params['email'],
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
      'HRJobDetails-period_end_date' => '2016-01-20',
    );

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::ERROR, $importResponse);
  }

  public function testMandatoryFieldsImportOnlyWillCreateRevisionForAllOtherEntities() {
    $contact2Params = [
      'first_name' => 'John_3',
      'last_name' => 'Snow_3',
      'email' => 'a3@b3.com',
      'contact_type' => 'Individual',
    ];
    $contactID = $this->createTestContact($contact2Params);
    $params = [
      'HRJobContract-email' => $contact2Params['email'],
      'HRJobDetails-title' => 'Test Contract Title',
      'HRJobDetails-position' => 'Test Contract Position',
      'HRJobDetails-contract_type' => $this->_contractTypeID,
      'HRJobDetails-period_start_date' => '2016-01-01',
    ];

    $importResponse = $this->runImport($params);
    $this->assertEquals(CRM_Import_Parser::VALID, $importResponse);

    $this->validateResult($contactID);

    $revision = civicrm_api3('HRJobContract', 'get', [
      'sequential' => 1,
      'api.HRJobContractRevision.get' => [],
      'contact_id' => $contactID,
    ])['values'][0];
    $revision = $revision['api.HRJobContractRevision.get']['values'][0];

    $entityFields = [
      'details_revision_id',
      'health_revision_id',
      'hour_revision_id',
      'leave_revision_id',
      'pay_revision_id',
      'pension_revision_id'
    ];

    foreach($entityFields as $field) {
      $this->assertNotEmpty($revision[$field]);
    }
  }

  private function runImport($params)  {
    $fields = array_keys($params);
    $values = array_values($params);
    $importObject = new CRM_Hrjobcontract_Import_Parser_Api($fields);
    $importObject->_importMode = CRM_Hrjobcontract_Import_Parser::IMPORT_CONTRACTS;
    $importObject->init();
    return $importObject->import(NULL, $values);
  }

  private function validateResult($contactID, $entity = NULL)  {
    $contract = civicrm_api3('HRJobContract', 'getsingle', ['contact_id' => $contactID]);
    $contractID = $contract['id'];
    $revision = civicrm_api3('HRJobContractRevision', 'getsingle', ['jobcontract_id' => $contractID]);
    $revisionID = $revision['details_revision_id'];
    $jobDetails = civicrm_api3('HRJobDetails', 'get', ['jobcontract_revision_id' => $revisionID]);
    $this->assertNotEmpty($jobDetails['values']);

    if ($entity !== NULl)  {
      switch ($entity) {
        case 'HRJobLeave':
          $jobLeaves = civicrm_api3($entity, 'get', ['jobcontract_revision_id' => $revisionID]);
          $this->assertCount(5, $jobLeaves['values']);
          break;

        case 'HRJobHealth':
          $jobHealth = civicrm_api3($entity, 'getsingle', ['jobcontract_revision_id' => $revisionID]);
          $this->assertEquals($this->_insurancePlanTypes[0]['value'], $jobHealth['plan_type']);
          $this->assertEquals($this->_insurancePlanTypes[1]['value'], $jobHealth['plan_type_life_insurance']);
          break;

        default:
          $result = civicrm_api3($entity, 'getsingle', ['jobcontract_revision_id' => $revisionID]);
          $this->assertNotEmpty($result);
          $this->assertFalse(array_key_exists('is_error', $result));
      }
    }
  }

  private function createTestContact($params)  {
    $contact = ContactFabricator::fabricate($params);

    return $contact['id'];
  }

  private function createTestContractType() {
    $contractType = OptionValueFabricator::fabricate([
      'option_group_id' => 'hrjc_contract_type',
      'name' => 'Test Contract Type',
      'label' => 'Test Contract Type',
      'sequential' => 1
    ]);

    return  $contractType['id'];
  }

  /**
   * Creates sample insurance plan types as option values to be used in tests.
   */
  public function createInsurancePlanTypes() {
    for ($i = 1; $i < 3; $i++) {
      $this->_insurancePlanTypes[] = OptionValueFabricator::fabricate([
        'option_group_id' => 'hrjc_insurance_plantype',
        'name' => 'PlanName_' . $i,
        'label' => 'Plan Label ' . $i,
        'value' => 'Plan Value ' . $i
      ]);
    }
  }

  private function validateHourAutoFields($contactID, $expected)  {
    $contract = civicrm_api3('HRJobContract', 'getsingle', [
      'contact_id' => $contactID
    ]);

    $revision = civicrm_api3('HRJobContractRevision', 'getsingle', [
      'jobcontract_id'=> $contract['id']
    ]);

    $jobHours = civicrm_api3('HRJobHour', 'getsingle', [
      'jobcontract_revision_id' => $revision['details_revision_id']
    ]);

    foreach($expected as $key => $value)  {
      $this->assertEquals($value, $jobHours[$key], "Failed asserting {$jobHours[$key]} matches expected $value for field $key");
    }
  }

  private function validatePayAutoFields($contactID, $expected)  {
    $contract = civicrm_api3('HRJobContract', 'getsingle', [
      'contact_id' => $contactID
    ]);

    $revision = civicrm_api3('HRJobContractRevision', 'getsingle', [
      'jobcontract_id'=> $contract['id']
    ]);

    $jobPay = civicrm_api3('HRJobPay', 'getsingle', [
      'jobcontract_revision_id' => $revision['details_revision_id']
    ]);

    foreach($expected as $key => $value)  {
      $this->assertEquals($value, $jobPay[$key]);
    }
  }

}

