<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.3                                                 |
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
    $this->openCiviPage('case/add', 'reset=1&action=add&atype=13&context=standalone', '_qf_Case_upload-bottom');

    // Adding contact with randomized first name (so we can then select that contact when creating case)
    // We're using pop-up New Contact dialog
    $firstName = substr(sha1(rand()), 0, 7);
    $lastName = "Fraser";
    $contactName = "{$lastName}, {$firstName}";
    $displayName = "{$firstName} {$lastName}";
    $email = "{$lastName}.{$firstName}@example.org";
    $this->webtestNewDialogContact($firstName, $lastName, $email, $type = 4);
    $caseTypeLabel = "Exiting";
    $activityTypes = array("Send Termination Letter", "Exit Interview");

    $caseStatusLabel = "Ongoing";
    $subject = "Employee resignaition";

    $details = "Employee work termination";
    $this->fireEvent('activity_details', 'focus');
    $this->fillRichTextField("activity_details", $details, 'CKEditor');
    $this->type("activity_subject", $subject);

    $this->select("case_type_id", "label={$caseTypeLabel}");
    $this->select("status_id", "label={$caseStatusLabel}");
    // Choose Case Start Date.
    $this->webtestFillDate('start_date', 'now');
    $today = date('F jS, Y', strtotime('now'));

    $this->clickLink("_qf_Case_upload-bottom", "_qf_CaseView_cancel-bottom");
    $this->waitForText('crm-notification-container', "Assignment opened successfully.");

    $this->completeActivity();
  }

  function completeActivity() {
    $this->waitForElementPresent("xpath=//form[@id='CaseView']/div[2]/div[@class='crm-accordion-wrapper crm-case_activities-accordion  crm-case-activities-block']/div[@id='activities']/div[@class='dataTables_wrapper']/table[@id='activities-selector']/tbody/tr[1]/td[7]/a[text()='Edit']");
    $this->click("xpath=//form[@id='CaseView']/div[2]/div[@class='crm-accordion-wrapper crm-case_activities-accordion  crm-case-activities-block']/div[@id='activities']/div[@class='dataTables_wrapper']/table[@id='activities-selector']/tbody/tr[1]/td[7]/a[text()='Edit']");
    $this->waitForPageToLoad($this->getTimeoutMsec());
    $this->verifyActivityDateChange();
  }

  function verifyActivityDateChange() {
    $prevactivityDate = date('F jS, Y', strtotime($this->getValue("activity_date_time")));

    sleep(2);
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

    $this->assertEquals($prevactivityDate, date('F jS, Y', strtotime($this->getValue("activity_date_time"))));
  }
}

