<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Class WebTest_PublicHoliday_FormTest
 *
 * @group headless
 */
class WebTest_PublicHoliday_FormTest extends CiviSeleniumTestCase implements HeadlessInterface, TransactionalInterface {

  private $formUrl = 'admin/leaveandabsences/public_holidays';
  private $addUrlParams = 'action=add&reset=1';

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  private function loginAsAdmin() {
    if (is_null($this->loggedInAs)) {
      $this->webtestLogin('admin');
    }
  }

  public function testCreateWithEmptyFields() {
    $this->loginAsAdmin();
    $this->openAddForm();
    $this->type('title', '');
    $this->type('date', '');
    $this->submitAndWait('PublicHoliday');
    $this->assertTrue($this->isTextPresent('Title is a required field.'));
    $this->assertTrue($this->isTextPresent('Date is a required field.'));
  }

  public function testCreateWithCurrentlyExistingDate() {
    $this->loginAsAdmin();
    $this->openAddForm();
    $this->type('title', 'Title 1');
    $this->type('date', '2016-06-01');
    $this->submitAndWait('PublicHoliday');
    $this->openAddForm();
    $this->type('title', 'Title 2');
    $this->type('date', '2016-06-01');
    $this->submitAndWait('PublicHoliday');
    $this->assertTrue($this->isTextPresent('Another Public Holiday with the same date already exists'));
  }

  public function testCreateValidPublicHoliday() {
    $this->loginAsAdmin();
    $this->openAddForm();
    $this->type('title', 'Valid Public Holiday');
    $this->type('date', '2020-01-01');
    $this->submitAndWait('PublicHoliday');
    $firstTdOfLastRow = 'xpath=//div[@class="form-item"]/table/tbody/tr[last()]/td[1]';
    $this->assertElementContainsText($firstTdOfLastRow, 'Valid Public Holiday');
  }

  private function openAddForm() {
    $this->openCiviPage($this->formUrl, $this->addUrlParams);
  }
}
