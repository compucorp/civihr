<?php

use Civi\Test\HeadlessInterface;

/**
 * Class WebTest_AbsencePeriod_FormTest
 *
 * @group headless
 */
class WebTest_AbsencePeriod_FormTest extends CiviSeleniumTestCase implements HeadlessInterface {

  private $formUrl = 'admin/leaveandabsences/periods';
  private $addUrlParams = 'action=add&reset=1';

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  private function loginAsAdmin() {
    if (is_null($this->loggedInAs)) {
      $this->webtestLogin('admin');
    }
  }

  public function testAddAnEmptyType() {
    $this->loginAsAdmin();
    $this->openAddForm();
    $this->type('start_date', '');
    $this->type('weight', '');
    $this->submitAndWait('AbsencePeriod');
    $this->assertTrue($this->isTextPresent('Title is a required field.'));
    $this->assertTrue($this->isTextPresent('Start Date is a required field.'));
    $this->assertTrue($this->isTextPresent('End Date is a required field.'));
    $this->assertTrue($this->isTextPresent('Order is a required field.'));
  }

  public function testCanAddPeriodWithMinimumRequiredFields() {
    $this->loginAsAdmin();
    $title = $this->addAbsencePeriod();
    $firstTdOfLastRow = 'xpath=//div[@class="form-item"]/table/tbody/tr[last()]/td[1]';
    $this->assertElementContainsText($firstTdOfLastRow, $title);
  }

  public function testStartDateShouldBeLessThanEndDate() {
    $this->loginAsAdmin();
    $this->openAddForm();
    $this->type('start_date', date('Y-m-d'));
    $this->type('end_date', date('Y-m-d', strtotime('-1 day')));
    $this->submitAndWait('AbsencePeriod');
    $this->assertTrue($this->isTextPresent('Start Date should be less than End Date'));
  }

  private function openAddForm() {
    $this->openCiviPage($this->formUrl, $this->addUrlParams);
  }

  private function addAbsencePeriod() {
    $this->openAddForm();

    $title = 'Title ' . microtime();
    $this->type('title', $title);

    $endDate = new DateTime($this->getValue('start_date'));
    $endDate->add(new DateInterval('P1D'));
    $this->type('end_date', $endDate->format('Y-m-d'));

    $this->submitAndWait('AbsencePeriod');

    return $title;
  }
}
