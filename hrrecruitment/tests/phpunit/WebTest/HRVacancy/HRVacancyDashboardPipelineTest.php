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
require_once 'CRM/Core/DAO/UFJoin.php';
require_once 'CRM/Core/Session.php';

class WebTest_HRVacancy_HRVacancyDashboardPipelineTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testVacancyOnDashboard() {
    $this->webtestLogin();
    $position = 'Junior Support Specialist ' . substr(sha1(rand()), 0, 7);
    $location = 'Home';
    $salary   = '$110-$130k/yr';

    $vacancy = $this->_createVacancy($position, $location, $salary);
    $newApplicantName = $this->_vacancyApply($vacancy['id']);
    $this->openCiviPage("vacancy/dashboard", "reset=1");
    $this->waitForText("xpath=//*[@id='crm-main-content-wrapper']/div/div[2]/table/tbody/tr[1]/td/div[2]", "a few seconds ago");
    $this->waitForElementPresent("xpath=//*[@id='crm-main-content-wrapper']/div/div[2]/table/tbody/tr[1]/td/div[1]/a[1]");
    $this->verifyText("xpath=//*[@id='crm-main-content-wrapper']/div/div[2]/table/tbody/tr[1]/td/div[1]/a[1]", $newApplicantName);
    $this->verifyText("xpath=//*['@id=crm-main-content-wrapper']/div/div[1]/div[1]/div[2]/div[1]/div/table/tbody/tr[1]/td/h3/a[1]", $position);
    $this->verifyText("//*[@id='crm-main-content-wrapper']/div/div[1]/div[1]/div[2]/div[1]/div/table/tbody/tr[4]/td/ul/li/a", '2');
   }

  function testRecentActivityOnDashboard() {
    $this->webtestLogin();
    $position = 'Junior Support Specialist ' . substr(sha1(rand()), 0, 7);
    $location = 'Home';
    $salary   = '$110-$130k/yr';

    $position = 'Junior Support Specialist ' . substr(sha1(rand()), 0, 7);
    $location = 'Home';
    $salary   = '$110-$130k/yr';
    $vacancy = $this->_createVacancy($position, $location, $salary);
    $this->openCiviPage("vacancy/apply", "reset=1&id=".$vacancy['id']);
    $fname1 = 'Trisha';
    $lname1 = substr(sha1(rand()), 0, 7);
    $applyContactName1 = "$lname1, $fname1";
    $email = "Trisha.{$lname1}@test.com";
    $this->type("first_name",$fname1);
    $this->type("last_name",$lname1);
    $this->type("xpath=//*[@id='email-Primary']", $email);
    $this->click("_qf_Application_upload");
    $this->waitForPageToLoad($this->getTimeoutMsec());
    $newApplicantName = "{$lname1}, {$fname1}";

    $this->openCiviPage("case/pipeline", "reset=1&vid={$vacancy['id']}");
    $this->waitForElementPresent('//*[@id="ui-tabs-1"]/div/div[1]/table/tbody/tr[1]/td[1]/input');
    $this->click('//*[@id="ui-tabs-1"]/div/div[1]/table/tbody/tr[1]/td[1]/input');
    $this->clickAt('//*[@id="s2id_autogen1"]/a');
    $this->waitForElementPresent('//*[@id="select2-results-2"]/li[1]');
    $name = $this->getText('//*[@id="select2-results-2"]/li[1]');
    $this->clickAt('//*[@id="select2-results-2"]/li[1]');
    $this->waitForElementPresent('//*[@id="_qf_Activity_upload-top"]');
    $this->_dashboardTestCommonDetail($name);
    $this->waitForText("crm-notification-container", "'Follow up' activity has been created.");
    $this->openCiviPage("vacancy/dashboard", "reset=1");
    if ($this->isElementPresent("xpath=//*[@id='crm-main-content-wrapper']/div/div[2]/table/tbody//tr[position()>1]/td/div[1]/a[text()='{$newApplicantName}']")) {
      $this->verifyText("xpath=//*[@id='crm-main-content-wrapper']/div/div[2]/table/tbody//tr[position()>1]/td/div[1]/a[text()='{$newApplicantName}']/parent::div/parent::td/div[2]", "an hour ago");
    }
    $this->verifyText("xpath=//*[@id='crm-main-content-wrapper']/div/div[2]/table/tbody//tr[position()=1]/td/div[1]/a[text()='{$newApplicantName}']/parent::div/parent::td/div[2]", "few seconds ago");

  }

  function testCasePipeline() {
    $this->webtestLogin();
    $position = 'Junior Support Specialist ' . substr(sha1(rand()), 0, 7);
    $location = 'Home';
    $salary   = '$110-$130k/yr';

    $vacancy = $this->_createVacancy($position, $location, $salary);
    $this->_vacancyApply($vacancy['id']);
    $this->openCiviPage("case/pipeline", "reset=1&vid={$vacancy['id']}");

    $this->waitForText("xpath=//*[@id='ui-tabs-1']/div/div[2]/div[2]", "0 applicants selected");

    $this->waitForElementPresent("xpath=//*[@id='ui-tabs-1']/div/div[1]/table/tbody/tr[1]/td[1]/input");
    $this->click("xpath=//*[@id='ui-tabs-1']/div/div[1]/table/tbody/tr[1]/td[1]/input");
    $name = $this->getText("xpath=//*[@id='ui-tabs-1']/div/div[1]/table/tbody//tr/td[2]");
    $this->verifyText("xpath=//*[@id='ui-tabs-1']/div/div[1]/table/tbody//tr/td[2]", $name);

    $this->waitForElementPresent("xpath=//*[@id='ui-tabs-1']/div/div[1]/table/tbody/tr[2]/td[1]/input");
    $this->click("xpath=//*[@id='ui-tabs-1']/div/div[1]/table/tbody/tr[2]/td[1]/input");
    $this->waitForText("xpath=//*[@id='ui-tabs-1']/div/div[2]/div[2]", "2 applicants selected");

    $this->click("xpath=//*[@id='ui-id-3']");
    $this->waitForText("xpath=//*[@id='ui-tabs-2']/div/div[2]/div[2]", "0 applicants selected");

    $this->click("xpath=//*[@id='ui-id-2']");
    $aftername = $this->getText('//*[@id="ui-tabs-1"]/div/div[1]/table/tbody/tr[1]/td[2]');

    $this->_changeCaseStatus();
    $this->click("xpath=//*[@id='ui-id-3']");
    $this->waitForElementPresent("xpath=//*[@id='ui-tabs-2']/div/div[1]/table/tbody/tr[1]/td[1]/input");
    $this->click("xpath=//*[@id='ui-tabs-2']/div/div[1]/table/tbody/tr[1]/td[1]/input");
    $this->verifyText("xpath=//*[@id='ui-tabs-2']/div/div[1]/table/tbody/tr[1]/td[2]", $aftername);
    $this->click("xpath=//*[@id='ui-id-4']");
    $this->waitForText("xpath=//*[@id='ui-tabs-3']/div/div[2]/div[2]", "0 applicants selected");
    $this->click("xpath=//*[@id='ui-id-2']");

    $this->_testCasePipelineComment();
    $this->openCiviPage("case/pipeline", "reset=1&vid={$vacancy['id']}");
    $this->_testCasePipelineAddActivity();
  }

  function _createVacancy($position, $location, $salary) {
    $params = array(
      'first_name' => 'Logged In',
      'last_name' => 'User ' . rand(),
      'contact_type' => 'Individual',
    );
    $result = $this->webtest_civicrm_api('contact', 'create', $params);
    $contactID = $result['id'];
    CRM_Core_Session::singleton()->set('userID', $contactID);

    $vacancy = $this->webtest_civicrm_api('HRVacancy', 'create', array (
      'version' => 3,
      'salary' => $salary,
      'position' => $position,
      'description' => 'Answer phone calls and emails from irate customers.',
      'benefits' => 'Have a place to park',
      'requirements' => 'Pro-actively looks to build cross discipline experience and increase knowledge.',
      'location' => $location,
      'is_template' => '0',
      'status_id' => 'Open',
      'start_date' => '2013-11-28 07:58:43',
      'end_date' => '2014-05-31 17:46:45',
      'created_id' => CRM_Core_Session::singleton()->get('userID'),
    ));
    $this->_vacancyStages($vacancy['id']);
    return $vacancy;
  }

  function _vacancyStages($vacancyID) {
    $this->webtest_civicrm_api('HRVacancyStage', 'create', array('vacancy_id' => $vacancyID, 'case_status_id' => '4', 'weigth' => '1'));
    $this->webtest_civicrm_api('HRVacancyStage', 'create', array('vacancy_id' => $vacancyID, 'case_status_id' => '6', 'weight' => '2'));
    $this->webtest_civicrm_api('HRVacancyStage', 'create', array('vacancy_id' => $vacancyID, 'case_status_id' => '7', 'weight' => '3'));
    $params = array(
      'sequential' => 1,
      'module' => 'Vacancy',
      'weight' => 1,
      'uf_group_id' => 20,
      'entity_table' => 'civicrm_vacancy',
      'entity_id' => $vacancyID,
      'module_data' => 'application_profile',
    );
    civicrm_api3('UFJoin', 'create', $params);
  }

  function _vacancyApply($vacancyID) {
    $this->openCiviPage("vacancy/apply", "reset=1&id=".$vacancyID);
    $fname1 = 'Michael';
    $lname1 = substr(sha1(rand()), 0, 7);
    $applyContactName1 = "$lname1, $fname1";
    $email = 'michael.{$lname1}@test.com';
    $this->type("first_name",$fname1);
    $this->type("last_name",$lname1);
    $this->type("xpath=//*[@id='email-Primary']", $email);
    $this->click("_qf_Application_upload");
    $this->waitForPageToLoad($this->getTimeoutMsec());

    $this->openCiviPage("vacancy/apply", "reset=1&id=".$vacancyID);
    $fname2 = 'John';
    $lname2 = substr(sha1(rand()), 0, 7);
    $applyContactName2 = "$lname2, $fname2";
    $email = 'john_{$lname2}@test.com';
    $this->type("first_name",$fname2);
    $this->type("last_name",$lname2);
    $this->type("xpath=//*[@id='email-Primary']", $email);
    $this->click("_qf_Application_upload");
    $this->waitForPageToLoad($this->getTimeoutMsec());
  }

  function _testCasePipelineComment() {
    $this->click('//*[@id="ui-tabs-1"]/div/div[1]/table/tbody/tr[1]/td[1]/input');
    $this->click('//*[@id="ui-tabs-1"]/div/div[2]/div[1]/a[2]');
    $this->waitForElementPresent('//*[@id="_qf_Activity_upload-bottom"]');
    $subject = "Test comment";
    $this->type("subject", $subject);
    $details = "Its contain detail comments.";
    $this->fillRichTextField("details", $details, 'CKEditor');
    $this->click('//*[@id="_qf_Activity_upload-top"]');
    $this->waitForText("crm-notification-container", "'Comment' activity has been created.");
  }

  function _testCasePipelineAddActivity() {
    $this->waitForElementPresent('//*[@id="ui-tabs-1"]/div/div[1]/table/tbody/tr[1]/td[1]/input');
    $this->click('//*[@id="ui-tabs-1"]/div/div[1]/table/tbody/tr[1]/td[1]/input');
    $this->clickAt('//*[@id="s2id_autogen1"]/a');

    $this->waitForElementPresent('//*[@id="select2-results-2"]/li[1]');
    $name = $this->getText('//*[@id="select2-results-2"]/li[1]');
    $this->clickAt('//*[@id="select2-results-2"]/li[1]');
    $this->waitForElementPresent('//*[@id="_qf_Activity_upload-top"]');
    $this->_commonAddActivity($name);
    $this->waitForText("crm-notification-container", "'Follow up' activity has been created.");
  }

  function _changeCaseStatus() {
    $this->click('//*[@id="ui-tabs-1"]/div/div[1]/table/tbody/tr[2]/td[1]/input');
    $this->clickAt('//*[@id="s2id_autogen3"]/a');
    $this->waitForElementPresent('//*[@id="select2-results-4"]/li[2]');
    $name = $this->getText('//*[@id="select2-results-4"]/li[2]');
    $this->clickAt('//*[@id="select2-results-4"]/li[2]');
    $this->waitForElementPresent('_qf_Activity_upload-top');
    $this->assertElementContainsText('//*[@id="Activity"]/div[2]/table/tbody/tr[1]/td[2]', $name);
    $this->click('//*[@id="_qf_Activity_upload-bottom"]');
    $this->waitForText("crm-notification-container", "Change Case Status' activity has been created.");
  }

  function _commonAddActivity($name) {
    $this->assertElementContainsText('//*[@id="Activity"]/div[2]/table[2]/tbody/tr[3]/td[2]', $name);
    $this->_commonDetail();
    $this->click("contact_check_0");
    $this->_commonFollowup();
    $caseStatusLabel = "Completed";
    $this->select("status_id", "label={$caseStatusLabel}");
    $priority = "Low";
    $this->select("priority_id", "label={$priority}");
    $this->click('//*[@id="_qf_Activity_upload-top"]');
  }

  function  _commonFollowup() {
    $followup = "Comment";
    $this->select("followup_activity_type_id", "label={$followup}");
    $this->webtestFillDateTime('followup_date', '+2 month 11:10PM');
    $this->type("followup_activity_subject", "Its testing of follow up activity");
    $this->waitForElementPresent('followup_assignee_contact_id');
    $this->waitForElementPresent("//*[@id='followup_assignee_contact_id']/../div/ul/li/input");
    $orgName = 'WestsideCoop2' . substr(sha1(rand()), 0, 7);
    $this->click("//*[@id='followup_assignee_contact_id']/../div/ul/li/input");
    $this->click("//*[@id='select2-drop']/ul/li/a[contains(text(),' New Organization')]");
    $this->waitForElementPresent('_qf_Edit_next');
    $this->type('organization_name', $orgName);
    $this->type("xpath=//div[@id='editrow-email-Primary']/div[2]/input[@class='medium crm-form-text']", "info@" . $orgName . ".com");
    $this->click('_qf_Edit_next');
    $this->waitForText("xpath=//div[@id='s2id_followup_assignee_contact_id']","$orgName");
  }

  function _commonDetail() {
    $subject = "Safe daytime setting - senior female";
    $this->type("subject", $subject);
    $this->webtestFillDateTime('activity_date_time', '+1 month 11:10PM');
    $details = "Its contain detail for testing.";
    $this->fillRichTextField("details", $details, 'CKEditor');
  }

  function _dashboardTestCommonDetail($name) {
    $this->assertElementContainsText('//*[@id="Activity"]/div[2]/table[2]/tbody/tr[3]/td[2]', $name);
    $subject = "Safe daytime setting - senior female";
    $location = "Main offices";
    $this->type("subject", $subject);
    $this->select("medium_id", "value=1");
    $this->type("location", $location);

    $currentDateTime = date("YmdHis");
    $date = date("Y-m-d");
    $addtime = -1;
    $currentTime = substr($currentDateTime,8,2).":".substr($currentDateTime,10,2);
    $beforeTime = date("H:i",strtotime($currentTime) + ($addtime*3600));

    $time1 = date("H", strtotime($currentTime));
    $time2 = date("H", strtotime($beforeTime));
    $diffTime = $time1 - $time2;

    $this->webtestFillDateTime('activity_date_time', "$date $beforeTime");
    $details = "Its contain detail for testing.";
    $this->fillRichTextField("details", $details, 'CKEditor');
    $this->type("duration", "20");

    $caseStatusLabel = "Completed";
    $this->select("status_id", "label={$caseStatusLabel}");
    $priority = "Low";
    $this->select("priority_id", "label={$priority}");
    $this->click('//*[@id="_qf_Activity_upload-top"]');
  }
}

