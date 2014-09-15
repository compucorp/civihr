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
class WebTest_HRQualification_HRQualificationAddEditTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testQualCreateEdit() {
    $this->webtestLogin();

    // Adding contacts
    $random = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($random, "Jameson", "$random@jameson.name");

    // Check if Qualifications tab exists
    $this->assertTrue($this->isElementPresent("xpath=//a[@title='Qualifications']"), 'Qualifications tab not appearing');

    //add Qualification data
    $addData = array(
      'Category_of_Skill' => 'Language',
      'Category_Name' => 'Java',
      'Level_of_Skill' => 'Basic',
      'Name_of_Certification' => $random,
      'Certification_Authority' => "XYZ Co.",
      'Grade_Achieved' => "B",
      'Attain_Date' => date("Y-m-d"),
      'Expiry_Date' => date("Y-m-d",strtotime("+1 month")),
      'Evidence_Note' => 'NA',
    );
    $this->_addQualificationData($addData, "add");

    //edit Qualification data
    $randomEditVisaNumber = substr(sha1(rand()), 0, 7);
    $editData = array(
      'Category_of_Skill' => 'Language',
      'Category_Name' => 'Java',
      'Level_of_Skill' => 'Basic',
      'Name_of_Certification' => $randomEditVisaNumber,
      'Certification_Authority' => "XYZ Co.",
      'Grade_Achieved' => "B",
      'Attain_Date' => date("Y-m-d"),
      'Expiry_Date' => date("Y-m-d",strtotime("+1 month")),
      'Evidence_Note' => 'NA',
    );
    $this->_addQualificationData($editData, "edit", $random);
  }

  function _addQualificationData($values, $mode = NULL, $nameOfCertification = NULL) {
    if ($mode == 'add') {
      $this->click("xpath=//a[@title='Qualifications']");
      $this->waitForElementPresent("xpath=//*[@id='ui-id-25']/a/span/div");
      $this->click("xpath=//*[@id='ui-id-25']/a/span/div");
    }
    else {
      $this->click("xpath=//a[@title='Qualifications']");
      $this->waitForElementPresent("xpath=//*[@id='ui-id-25']/a/span/div");
      $this->click("xpath=//div[@id='custom-11-table-wrapper']//table/tbody/tr/td[text()='".$nameOfCertification."']/following-sibling::td[6]/span/a[text()='Edit']");
    }
    $this->waitForElementPresent("xpath=//select[@data-crm-custom='Qualifications:Category_of_Skill']");
    $this->type("xpath=//input[@data-crm-custom='Qualifications:Name_of_Skill']", $values['Category_Name']);
    $this->select("xpath=//select[@data-crm-custom='Qualifications:Category_of_Skill']", "label=".$values["Category_of_Skill"]);
    $this->select("xpath=//select[@data-crm-custom='Qualifications:Level_of_Skill']", "label=".$values["Level_of_Skill"]);
    $this->click("xpath=//label[text() = 'Yes']/preceding-sibling::input[1]");
    $this->type("xpath=//input[@data-crm-custom='Qualifications:Name_of_Certification']", $values['Name_of_Certification']);
    $this->type("xpath=//input[@data-crm-custom='Qualifications:Certification_Authority']", $values['Certification_Authority']);
    $this->type("xpath=//input[@data-crm-custom='Qualifications:Grade_Achieved']", $values['Grade_Achieved']);
    $this->type("xpath=//input[@data-crm-custom='Qualifications:Attain_Date']", $values['Attain_Date']);
    $this->type("xpath=//input[@data-crm-custom='Qualifications:Expiry_Date']", $values['Expiry_Date']);
    $this->type("xpath=//textarea[@data-crm-custom='Qualifications:Evidence_Note']", $values['Evidence_Note']);
    $this->click("xpath=//input[@id='_qf_CustomData_upload']");
    $this->waitForElementPresent("xpath=//a[@title='Qualifications']");
    sleep(2);
    $this->assertTrue($this->isTextPresent($values['Name_of_Certification']), 'Name of Certification not found after '.$mode.'ing Qualification (_addQualificationData).');
    $this->click("xpath=//div[@id='custom-11-table-wrapper']//table/tbody/tr/td[text()='".$values['Name_of_Certification']."']/following-sibling::td[6]/span/a[text()='View']");
    $this->assertTrue($this->isTextPresent($values['Name_of_Certification']), 'Name of Certification not found after '.$mode.'ing Qualification (_addQualificationData).');

    // WAS: xpath=//div[8]/div[1]/a
    $close = "xpath=//button[contains(concat(' ',normalize-space(@class),' '),' ui-dialog-titlebar-close ')]";
    $this->waitForElementPresent($close);
    $this->click($close);
  }

}
