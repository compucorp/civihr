<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.2                                                 |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
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

class WebTest_HRVacancy_HRAddEditVacancyTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function _addVacancyDetails($values) {
    $this->type('position', $values['position']);
    $this->select('location', "value={$values['location']}");
    $this->type('salary', $values['salary']);
    $this->fillRichTextField("description", $values['description'], 'CKEditor');
    $this->fillRichTextField("benefits", $values['benefits'], 'CKEditor');
    $this->fillRichTextField("requirements", $values['requirements'], 'CKEditor');
    $this->webtestFillDateTime("start_date", "+1 week");
    $this->webtestFillDateTime("end_date", "+1 week 1 day 8 hours ");
    if (!empty($values['status'])) {
      $this->select("status_id", $values['status']);
    }
    foreach ($values['stages'] as $stage) {
      $this->addSelection("stages-f", "label=$stage");
      $this->click("//option['$stage']");
      $this->click("add");
    }
    $count = 1;
    foreach ($values['permission'] as $contactName => $permission) {
      $this->webtestFillAutocomplete($contactName, "s2id_permission_contact_id_$count");
      $this->select("permission_$count", $permission);
      $this->click('addMorePermission');
      $count++;
    }
    $this->click("xpath=//*[@id='_qf_HRVacancy_next']");
  }

  function testAddEditVacancyNoTemplate() {
    $this->webtestLogin();
    $this->openCiviPage("vacancy/add", "reset=1");
    $this->waitForElementPresent("template_id");

    $fname1 = 'Michael';
    $lname1 = substr(sha1(rand()), 0, 7);
    $permContactName1 = "$lname1, $fname1";
    $this->webtest_civicrm_api('Contact', 'create', array('first_name' => $fname1, 'last_name' => $lname1, 'email' => substr(sha1(rand()), 0, 7) . 'b@d.com', 'contact_type' => 'Individual'));
    $fname2 = 'Sandrea';
    $lname2 = substr(sha1(rand()), 0, 7);
    $permContactName2 = "$lname2, $fname2";
    $this->webtest_civicrm_api('Contact', 'create', array('first_name' => $fname2, 'last_name' => $lname2, 'email' => substr(sha1(rand()), 0, 7) . 'b@d.com', 'contact_type' => 'Individual'));

    $vacancy = array(
      'position' => 'Junior Support Specialist ' . substr(sha1(rand()), 0, 7),
      'location' => 'Home',
      'salary' => '$110-$130k/yr',
      'description' => 'Answer phone calls and emails from irate customers.',
      'benefits' => 'Have a place to park',
      'requirements' => 'Pro-actively looks to build cross discipline experience and increase knowledge.',
      'status' => 'Open',
      'stages' => array('Apply', 'Phone Interview', 'Manager Interview', 'Psych Exam', 'Offer', 'Hired'),
      'permission' => array($permContactName1 => 'Manage Applicants', $permContactName2 => 'Administer Vacancy'),
    );

    $this->_addVacancyDetails($vacancy);

    $this->waitForElementPresent("xpath=//*[@id='Search']");
    $vid = $this->webtest_civicrm_api('HRVacancy', 'getvalue', array('position' => $vacancy['position'], 'return' => 'id'));
    $this->verifyText("xpath=//*[@id='$vid']/td[1]/a", $vacancy['position']);

    //edit just created vacancy
    $this->clickLink("xpath=//*[@id='$vid']/td[6]/span/a");
    $editVacancyParam = array(
      'position' => "{$vacancy['position']} Edited",
      'location' => 'Headquarters',
      'status' => 'Draft',
    );
    $this->type('position', $editVacancyParam['position']);
    $this->select("location", "value={$editVacancyParam['location']}");
    $this->select("status_id", $editVacancyParam['status']);
    $this->click("xpath=//*[@id='_qf_HRVacancy_next']");
    $this->waitForElementPresent("xpath=//*[@id='Search']");
    $this->verifyText("xpath=//*[@id='$vid']/td[1]/a", $editVacancyParam['position']);
    $this->verifyText("xpath=//*[@id='$vid']/td[2]", $editVacancyParam['location']);
    $this->verifyText("xpath=//*[@id='$vid']/td[5]", $editVacancyParam['status']);
  }

  function testAddEditVacancyWithTemplate() {
    $this->webtestLogin();
    $this->openCiviPage("vacancy/add", "reset=1&template=1");

    $fname1 = 'Michael';
    $lname1 = substr(sha1(rand()), 0, 7);
    $permContactName1 = "$lname1, $fname1";
    $this->webtest_civicrm_api('Contact', 'create', array('first_name' => $fname1, 'last_name' => $lname1, 'email' => substr(sha1(rand()), 0, 7) . 'b@d.com', 'contact_type' => 'Individual'));
    $fname2 = 'Sandrea';
    $lname2 = substr(sha1(rand()), 0, 7);
    $permContactName2 = "$lname2, $fname2";
    $this->webtest_civicrm_api('Contact', 'create', array('first_name' => $fname2, 'last_name' => $lname2, 'email' => substr(sha1(rand()), 0, 7) . 'b@d.com', 'contact_type' => 'Individual'));

    $vacancy = array(
      'position' => 'Senior Support Specialist ' . substr(sha1(rand()), 0, 7),
      'location' => 'Home',
      'salary' => '$110-$130k/yr',
      'description' => 'Answer phone calls and emails from irate customers.',
      'benefits' => 'Have a place to park',
      'requirements' => 'Pro-actively looks to build cross discipline experience and increase knowledge.',
      'stages' => array('Apply', 'Phone Interview', 'Manager Interview', 'Psych Exam', 'Offer', 'Hired'),
      'permission' => array($permContactName1 => 'Manage Applicants', $permContactName2 => 'Administer Vacancy'),
    );

    $this->_addVacancyDetails($vacancy);
    $this->waitForElementPresent("xpath=//*[@id='Search']");
    $vid = $this->webtest_civicrm_api('HRVacancy', 'getvalue', array('position' => $vacancy['position'], 'return' => 'id'));
    $this->verifyText("xpath=//*[@id='$vid']/td[1]/a", $vacancy['position']);

    //edit just created vacancy template
    $this->clickLink("xpath=//*[@id='$vid']/td[5]/span/a");
    $editVacancyParam = array(
      'position' => "{$vacancy['position']} Edited Template",
      'location' => 'Headquarters',
    );
    $this->type('position', $editVacancyParam['position']);
    $this->select("location", "value={$editVacancyParam['location']}");
    $this->click("xpath=//*[@id='_qf_HRVacancy_next']");
    $this->waitForElementPresent("xpath=//*[@id='Search']");
    $this->verifyText("xpath=//*[@id='$vid']/td[1]/a", $editVacancyParam['position']);
    $this->verifyText("xpath=//*[@id='$vid']/td[2]", $editVacancyParam['location']);
  }
}

