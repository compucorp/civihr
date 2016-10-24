<?php

require_once EXTENSION_ROOT_DIR . 'CRM/HRSampleData/Importer/AbsenceType.php';

/**
 * Class CRM_HRSampleData_Importer_AbsenceTypeTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_AbsenceTypeTest extends CRM_HRSampleData_BaseImporterTest {

  private $rows;

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->install('org.civicrm.hrabsence')
      ->apply();
  }

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testImport() {
    $this->rows[] = [
      'Compassionate_Leave',
      'Compassionate Leave',
      1,
      0,
      1,
    ];

    $this->runImporter('CRM_HRSampleData_Importer_AbsenceType', $this->rows[]);

    $this->assertEquals('Compassionate_Leave', $this->apiQuickGet('HRAbsenceType','name', 'Compassionate_Leave'));
  }

  private function importHeadersFixture() {
    return [
      'name',
      'title',
      'is_active',
      'allow_credits',
      'allow_debits',
    ];
  }

}
