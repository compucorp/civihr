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
class WebTest_HRIdentification_HRIdentificationAddEditTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testIdentificationCreateEdit() {
    $this->webtestLogin();

    // Adding contacts
    $random = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($random, "Jameson", "$random@jameson.name");

    // Check if Identification tab exists
    $this->waitForElementPresent("xpath=//li[@aria-controls='Identification']");

    //add Visa data
    $addData = array(
      'Type' => 'Drivers License',
      'Number' => $random,
      'Issue_Date' => date("Y-m-d"),
      'Expire_Date' => date("Y-m-d",strtotime("+1 month")),
      'State_Province' => "American Samoa",
      'Country' => "United States",
      'Evidence_Note' => 'NA',
    );
    $this->_addIdentificationData($addData, "add");
    
    //edit Visa data
    $randomEditVisaNumber = substr(sha1(rand()), 0, 7);
    $editData = array(
      'Type' => 'Drivers License',
      'Number' => $randomEditVisaNumber,
      'Issue_Date' => date("Y-m-d"),
      'Expire_Date' => date("Y-m-d",strtotime("+1 month")),
      'State_Province' => "American Samoa",
      'Country' => "United States",
      'Evidence_Note' => 'NA',
    );
    $this->_addIdentificationData($editData, "edit", $random);
  }

  function _addIdentificationData($values, $mode = NULL, $number = NULL) {
    if ($mode == 'add') {
      $this->click("xpath=//a[@title='Identification']");
      $this->waitForElementPresent("xpath=//form[@id='Edit']/div[2]/a/span");
      $this->click("xpath=//form[@id='Edit']/div[2]/a/span");
    }  	
    else {
      $this->click("xpath=//a[@title='Identification']");
      $this->waitForElementPresent("xpath=//form[@id='Edit']/div[2]/a/span");
      $this->click("xpath=//div[@id='browseValues']//table/tbody/tr/td[text()='".$number."']/following-sibling::td[6]/span/a[text()='Edit']");
    }
    $this->waitForElementPresent("xpath=//select[@data-crm-custom='Identify:Type']");
    $this->select("xpath=//select[@data-crm-custom='Identify:Type']", "label=".$values["Type"]);
    $this->type("xpath=//input[@data-crm-custom='Identify:Number']", $values['Number']);
    $this->type("xpath=//input[@data-crm-custom='Identify:Issue_Date']", $values['Issue_Date']);
    $this->type("xpath=//input[@data-crm-custom='Identify:Expire_Date']", $values['Expire_Date']);
    $this->select("xpath=//div[@class='crm-profile-name-hrident_tab']/div/div[5]/div[2]/select", "label=".$values["State_Province"]);
    $this->select("xpath=//select[@data-crm-custom='Identify:Country']", "label=".$values["Country"]);
    $this->type("xpath=//textarea[@data-crm-custom='Identify:Evidence_Note']", $values['Evidence_Note']);
    $this->click("xpath=//input[@id='_qf_Edit_upload']");
    $this->waitForPageToLoad($this->getTimeoutMsec());
    $this->waitForElementPresent("xpath=//li[@aria-controls='Identification']");
    sleep(2);
    $this->assertTrue($this->isTextPresent($values['Number']), 'Number not found after '.$mode.'ing Identification (_addIdentificationData).');
    $this->click("xpath=//div[@id='browseValues']//table/tbody/tr/td[text()='".$values['Number']."']/following-sibling::td[6]/span/a[text()='View']");
    $this->assertTrue($this->isTextPresent($values['Number']), 'Number not found after '.$mode.'ing Identification (_addIdentificationData).');

    // WAS: xpath=//div[8]/div[1]/a
    $close = "xpath=//a[contains(concat(' ',normalize-space(@class),' '),' ui-dialog-titlebar-close ')]";
    $this->waitForElementPresent($close);
    $this->click($close);
  }

}

