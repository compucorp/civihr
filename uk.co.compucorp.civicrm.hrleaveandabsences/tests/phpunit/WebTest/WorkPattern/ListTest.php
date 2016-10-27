<?php

require_once 'FormHelpersTrait.php';

/**
 * Class WebTest_WorkPattern_ListTest
 *
 * @group headless
 */
class WebTest_WorkPattern_ListTest extends CiviSeleniumTestCase {

  use WebTest_WorkPattern_FormHelpersTrait;

  private function loginAsAdmin() {
    if (is_null($this->loggedInAs)) {
      $this->webtestLogin('admin');
    }
  }

  public function testIfPatternHasOneWeekItShowsTheNumberOfHoursOnTheList() {
    $this->loginAsAdmin();
    $this->addWorkPatternWithOneWeekAnd40Hours();
    $this->assertElementContainsText(
      $this->getNumberOfWeeksOfTheLastInsertedPatternLocator(),
      '1'
    );
    $this->assertElementContainsText(
      $this->getNumberOfHoursOfTheLastInsertedPatternLocator(),
      '40'
    );
  }

  public function testIfPatternHasMoreThanOneWeekItShowsVariousAsTheNumberOfHoursOnTheList() {
    $this->loginAsAdmin();
    $this->addWorkPatternWithTwoWeeks();
    $this->assertElementContainsText(
      $this->getNumberOfWeeksOfTheLastInsertedPatternLocator(),
      '2'
    );
    $this->assertElementContainsText(
      $this->getNumberOfHoursOfTheLastInsertedPatternLocator(),
      'Various'
    );
  }

  public function getNumberOfWeeksOfTheLastInsertedPatternLocator() {
    return 'xpath=//table/tbody/tr[last()]/td[3]';
  }

  public function getNumberOfHoursOfTheLastInsertedPatternLocator() {
    return 'xpath=//table/tbody/tr[last()]/td[4]';
  }
}
