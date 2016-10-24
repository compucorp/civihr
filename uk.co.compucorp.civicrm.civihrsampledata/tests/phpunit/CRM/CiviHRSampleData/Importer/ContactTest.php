<?php

require_once EXTENSION_ROOT_DIR . 'CRM/CiviHRSampleData/Importer/Contact.php';

/**
 * Class CRM_CiviHRSampleData_Importer_ContactTest
 *
 * @group headless
 */
class CRM_CiviHRSampleData_Importer_ContactTest extends CRM_CiviHRSampleData_BaseImporterTest {

  private $rows;

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testImport() {
    $this->rows[] = [
      '235',
      'Individual',
      '',
      'Agodi, Peter',
      'Mr. Peter Agodi',
      '',
      '',
      '235_2abab863cb821885805697348c4527e6.png',
      'en_US',
      'Both',
      '7c45cf9b0882dfd1e69a93a42e6e51bd',
      'CiviHR Sample Data',
      'Peter',
      '',
      'Agodi',
      'Mr.',
      '',
      1,
      'Dear Ellissa',
      1,
      'Dear Ellissa',
      1,
      'Ms. Ellissa Agodi',
      'Male',
      '1957-10-02',
      '',
      0,
    ];

    $this->runImporter('CRM_CiviHRSampleData_Importer_Contact', $this->rows);

    $this->assertEquals('Mr. Peter Agodi', $this->apiQuickGet('Contact', 'display_name', 'Mr. Peter Agodi'));
  }

  private function importHeadersFixture() {
    return [
      'id',
      'contact_type',
      'contact_sub_type',
      'sort_name',
      'display_name',
      'nick_name',
      'legal_name',
      'image_URL',
      'preferred_language',
      'preferred_mail_format',
      'hash',
      'source',
      'first_name',
      'middle_name',
      'last_name',
      'prefix_id',
      'suffix_id',
      'email_greeting_id',
      'email_greeting_display',
      'postal_greeting_id',
      'postal_greeting_display',
      'addressee_id',
      'addressee_display',
      'gender_id',
      'birth_date',
      'organization_name',
      'is_deleted',
    ];
  }

}
