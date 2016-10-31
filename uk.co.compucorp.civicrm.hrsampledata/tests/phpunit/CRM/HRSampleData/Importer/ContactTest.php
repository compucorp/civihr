<?php

/**
 * Class CRM_HRSampleData_Importer_ContactTest
 *
 * @group headless
 */
class CRM_HRSampleData_Importer_ContactTest extends CRM_HRSampleData_BaseImporterTest {

  public function setUp() {
    $this->rows = [];
    $this->rows[] = $this->importHeadersFixture();
  }

  public function testIterate() {
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

    $this->runIterator('CRM_HRSampleData_Importer_Contact', $this->rows);

    $contact = $this->apiGet('Contact', ['display_name' => 'Mr. Peter Agodi']);

    $this->assertEquals('Mr. Peter Agodi', $contact['display_name']);
    $this->assertEquals('Agodi, Peter', $contact['sort_name']);
    $this->assertEquals('en_US', $contact['preferred_language']);
    $this->assertEquals('1957-10-02', $contact['birth_date']);
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
