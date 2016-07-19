<?php

use Civi\Test\HeadlessInterface;

/**
 * Class WebTest_AbsenceType_FormTest
 *
 * @group headless
 */
class WebTest_ManageEntitlements_AdvanceSearchTaskTest extends CiviSeleniumTestCase implements HeadlessInterface {

  private $advancedSearchUrl = 'contact/search/advanced';

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

  private function loginAsAdmin() {
    if(is_null($this->loggedInAs)) {
      $this->webtestLogin('admin');
    }
  }

  public function testManageLeaveEntitlementsTaskExists() {
    $this->loginAsAdmin();
    $this->openAdvancedSearch();
    $this->searchForIndividuals();
    $this->selectAllRecords();
    $this->selectAndWait('task', 'Manage leave entitlements');
    //Checks if the absence period dropdown is visible
    $this->assertTrue($this->isVisible('absence_period'));
  }

  private function openAdvancedSearch() {
    $this->openCiviPage($this->advancedSearchUrl, 'reset=1');
  }

  private function searchForIndividuals() {
    $this->select('contact_type', "value=Individual");
    $this->submitAndWait('Advanced');
  }

  private function selectAllRecords() {
    $this->click('CIVICRM_QFID_ts_all_10');
    sleep(1);
  }

}
