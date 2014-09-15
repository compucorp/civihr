<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.4                                                 |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License along with this program; if not, contact CiviCRM LLC       |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviSeleniumTestCase.php';
class WebTest_HRCase_HRCaseActivityTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testJobCreateEdit() {
    $this->webtestLogin();
    $this->addCase();
  }

  function addCase() {
    // Adding contact with randomized first name (so we can then select that contact when creating case)
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Jameson", "$firstName@jameson.name");
    $this->openCiviPage('case/add', 'reset=1&action=add&atype=13&context=standalone', '_qf_Case_upload-bottom');

    $caseTypeLabel = "Exiting";
    $caseStatusLabel = "Ongoing";
    $subject = "Employee resignaition";
    $details = "Employee work termination";
    $this->select2('client_id', $firstName);
    $this->waitForElementPresent('activity_details');
    $this->fillRichTextField("activity_details", $details, 'CKEditor');
    $this->type("activity_subject", $subject);
    $this->select("case_type_id", "label={$caseTypeLabel}");
    $this->select("status_id", "label={$caseStatusLabel}");
    // Choose Case Start Date.
    $this->webtestFillDate('start_date', 'now');
    $this->clickLink("_qf_Case_upload-bottom", "_qf_CaseView_cancel-bottom");
    $this->waitForText('crm-notification-container', "Assignment opened successfully.");

    $this->completeActivity();
  }

  function completeActivity() {
   $this->waitForElementPresent("xpath=//div[@id='activities']/div[2]/table/tbody/tr/td[7]/a[text()='Edit']");
   $this->click("xpath=//div[@id='activities']/div[2]/table/tbody/tr/td[7]/a[text()='Edit']");
   $this->verifyActivityDateChange();
  }

  function verifyActivityDateChange() {
    sleep(2);
    $this->waitForElementPresent('status_id');
    $this->select("status_id", "label=Completed");
    $this->waitForText('crm-notification-container', "Updated Completion Time");
    $today = date('F jS, Y', strtotime('now'));
    $now = date("h:iA",strtotime('now'));
    sleep(2);

    $activityDate = date('F jS, Y', strtotime($this->getValue("activity_date_time")));
    $time = $this->getValue("activity_date_time_time");
    $this->assertEquals($today, $activityDate);
    $this->assertEquals($now, $time);
    $this->click("xpath=//span[@id='revert-link']/a");
    sleep(2);

    $this->assertEquals($activityDate, date('F jS, Y', strtotime($this->getValue("activity_date_time"))));
  }
}
