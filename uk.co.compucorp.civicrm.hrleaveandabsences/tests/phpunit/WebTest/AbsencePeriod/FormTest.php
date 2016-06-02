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

  public function testAddAnEmptyPeriod() {
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
    $this->type('title', 'Test Absence Period');
    $this->type('start_date', date('Y-m-d'));
    $this->type('end_date', date('Y-m-d', strtotime('-1 day')));
    $this->submitAndWait('AbsencePeriod');
    $this->assertTrue($this->isTextPresent('Start Date should be less than End Date'));
  }

  public function testShouldDisplayAConfirmationWhenSavingAPeriodWithTheSameOrderNumberOfAnExistingPeriod()
  {
    $this->loginAsAdmin();
    $this->addAbsencePeriod();
    $this->addAbsencePeriod(false);

    // When adding a new period, the order is automatically
    // max weight + 1. So, if we subtract 1 from it we'll
    // get the order number of last added period
    $periodOrder = (int)$this->getValue('weight') - 1;

    $this->type('weight', $periodOrder);
    $this->click("xpath=id('_qf_AbsencePeriod_next-bottom')");

    $confirmationDialog = "xpath=//div[contains(@class, 'crm-confirm-dialog')]";
    $this->waitForElementPresent($confirmationDialog);
    $confirmationMessage = 'Another period has this order number. ' .
                           'If you choose to continue all periods ' .
                           'with the same or greater order number ' .
                           'will be increased by 1 and hence will ' .
                           'follow this period';
    $this->assertElementContainsText($confirmationDialog, $confirmationMessage);
  }

  public function testStartAndEndDatesShouldBeValidDates()
  {
    $this->loginAsAdmin();
    $this->openAddForm();

    $randomString = CRM_Utils_String::createRandom(rand(1, 10), 'abcdefghijklmnopqrstuvwxyz');
    $this->type('start_date', $randomString);
    $this->type('end_date', $randomString);
    $this->submitAndWait('AbsencePeriod');
    $this->assertTrue($this->isTextPresent('Start Date should be a valid date'));
    $this->assertTrue($this->isTextPresent('End Date should be a valid date'));

    $randomNumber = rand(1, PHP_INT_MAX);
    $this->type('start_date', $randomNumber);
    $this->type('end_date', $randomNumber);
    $this->submitAndWait('AbsencePeriod');
    $this->assertTrue($this->isTextPresent('Start Date should be a valid date'));
    $this->assertTrue($this->isTextPresent('End Date should be a valid date'));

    $incompleteDate = '2016-01';
    $this->type('start_date', $incompleteDate);
    $this->type('end_date', $incompleteDate);
    $this->submitAndWait('AbsencePeriod');
    $this->assertTrue($this->isTextPresent('Start Date should be a valid date'));
    $this->assertTrue($this->isTextPresent('End Date should be a valid date'));

    $this->type('start_date', '2016-02-32');
    $this->type('end_date', '2016-41-01');
    $this->submitAndWait('AbsencePeriod');
    $this->assertTrue($this->isTextPresent('Start Date should be a valid date'));
    $this->assertTrue($this->isTextPresent('End Date should be a valid date'));
  }

  private function openAddForm() {
    $this->openCiviPage($this->formUrl, $this->addUrlParams);
  }

  private function addAbsencePeriod($submit = true) {
    $this->openAddForm();

    $title = 'Title ' . microtime();
    $this->type('title', $title);

    $this->type('start_date', $this->getValue('start_date'));
    $endDate = new DateTime($this->getValue('start_date'));
    $endDate->add(new DateInterval('P1D'));
    $this->type('end_date', $endDate->format('Y-m-d'));

    if($submit) {
      $this->submitAndWait('AbsencePeriod');
    }

    return $title;
  }
}
