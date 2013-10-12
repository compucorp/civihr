<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.0                                                 |
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
class WebTest_HRCareer_HRCareerAddEditTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testMedCreateEdit() {
    $this->webtestLogin();

    // Adding contacts
    $random = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($random, "Jameson", "$random@jameson.name");
    
    // Check if Carrer tab exists
    $this->assertTrue($this->isElementPresent("xpath=//li[@aria-controls='Career']"), 'Career tab not appearing');

    //add Carrer data
    $addData = array(
      'Start_Date' => date("Y-m-d"),	
      'End_Date' => date("Y-m-d",strtotime("+1 month")),
      'Name_of_Organisation' => $random,
      'Occupation_Type' => "Salaried Employment",
      'Job_Title_Course_Name' => "XYZ",
      'Full_time_Part_time' => "Full-time",
      'Paid_Unpaid' => "Paid",
      'Reference_Supplied' => "ABC",
      'Evidence_Note' => 'NA',
    );
    $this->_addMedData($addData, "add");
    
    //edit Carrer data
    $randomEditCarrerNumber = substr(sha1(rand()), 0, 7);
    $editData = array(
      'Start_Date' => date("Y-m-d"),	
      'End_Date' => date("Y-m-d",strtotime("+1 month")),
      'Name_of_Organisation' => $randomEditCarrerNumber,
      'Occupation_Type' => "Salaried Employment",
      'Job_Title_Course_Name' => "XYZ",
      'Full_time_Part_time' => "Full-time",
      'Paid_Unpaid' => "Paid",
      'Reference_Supplied' => "ABC",
      'Evidence_Note' => 'NA',
    );
    $this->_addMedData($editData, "edit", $random);
 
  }

  function _addMedData($values, $mode = NULL, $nameOfOrganisation = NULL) {
    if ($mode == 'add') {
      $this->click("xpath=//a[@title='Career']");
      $this->waitForElementPresent("xpath=//form[@id='Edit']/div[2]/a/span");
      $this->click("xpath=//form[@id='Edit']/div[2]/a/span");
    }  	
    else {
      $this->click("xpath=//a[@title='Career']");
      $this->waitForElementPresent("xpath=//form[@id='Edit']/div[2]/a/span");
      $this->click("xpath=//div[@id='browseValues']//table/tbody/tr/td[text()='".$nameOfOrganisation."']/following-sibling::td[6]/span/a[text()='Edit']");
    }
    $this->waitForElementPresent("xpath=//input[@data-crm-custom='Career:Start_Date']");
    $this->type("xpath=//input[@data-crm-custom='Career:Start_Date']", $values['Start_Date']);
    $this->type("xpath=//input[@data-crm-custom='Career:End_Date']", $values['End_Date']);
    $this->type("xpath=//input[@data-crm-custom='Career:Name_of_Organisation']", $values['Name_of_Organisation']);
    $this->select("xpath=//select[@data-crm-custom='Career:Occupation_Type']", "label=".$values["Occupation_Type"]);
    $this->type("xpath=//input[@data-crm-custom='Career:Job_Title_Course_Name']", $values['Job_Title_Course_Name']);
    $this->select("xpath=//select[@data-crm-custom='Career:Full_time_Part_time']", "label=".$values["Full_time_Part_time"]);
    $this->select("xpath=//select[@data-crm-custom='Career:Paid_Unpaid']", "label=".$values["Paid_Unpaid"]);
    $this->type("xpath=//input[@data-crm-custom='Career:Reference_Supplied']", $values['Reference_Supplied']);
    $this->type("xpath=//textarea[@data-crm-custom='Career:Evidence_Note']", $values['Evidence_Note']);
    $this->click("xpath=//input[@id='_qf_Edit_upload']");
    $this->waitForPageToLoad($this->getTimeoutMsec());
    $this->waitForElementPresent("xpath=//li[@aria-controls='Career']");
    sleep(2);
    $this->assertTrue($this->isTextPresent($values['Name_of_Organisation']), 'Name of Organisation not found after '.$mode.'ing Career (_addMedData).');
    $this->click("xpath=//div[@id='browseValues']//table/tbody/tr/td[text()='".$values['Name_of_Organisation']."']/following-sibling::td[6]/span/a[text()='View']");
    $this->assertTrue($this->isTextPresent($values['Name_of_Organisation']), 'Name of Organisation not found after '.$mode.'ing Career (_addMedData).');

    // WAS: xpath=//div[8]/div[1]/a
    $close = "xpath=//a[contains(concat(' ',normalize-space(@class),' '),' ui-dialog-titlebar-close ')]";
    $this->waitForElementPresent($close);
    $this->click($close);
  }

}

