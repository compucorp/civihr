<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/BankDetails.php';

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_HRSampleData_Importer_BankDetailsTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_BankDetailsTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  private $testContact;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('uk.co.compucorp.civicrm.hrcore')
      ->install('org.civicrm.hrbank')
      ->apply();
  }

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();

    $this->testContact = ContactFabricator::fabricate(['first_name' => 'chrollo', 'last_name' => 'lucilfer']);
  }

  public function testImport() {
    $this->rows[] = [
      $this->testContact['id'],
      'Peter Agodi',
      '15-37-89',
      '111125431',
      'BarclaysBank PLC',
      'Wandsworth',
    ];

    $mapping = [
      ['contact_mapping', $this->testContact['id']],
    ];

    $this->runImporter('CRM_HRSampleData_Importer_BankDetails', $this->rows, $mapping);

    $this->assertNotEmpty($this->apiQuickGet('CustomValue','entity_id', $this->testContact['id']));
  }

  private function importHeadersFixture() {
    return [
      'entity_id',
      'Account_Holder',
      'Sort_Code',
      'Account_No',
      'Bank_Name',
      'Bank_Address_Line_1',
    ];
  }

}
