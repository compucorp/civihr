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
class WebTest_HRMed_HRMedAddEditTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testMedCreateEdit() {
    $this->webtestLogin();

    // Adding contacts
    $random = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($random, "Jameson", "$random@jameson.name");
    
    // Check if Medical & Disability tab exists
    $this->assertTrue($this->isElementPresent("xpath=//li[@aria-controls='Medical__Disability']"), 'Medical & Disability tab not appearing');

    //add Medical & Disability data
    $addData = array(
      'Condition' => $random,
      'Type' => 'Cognitive',
      'Evidence_Note' => 'NA',
    );
    $this->_addMedData($addData, "add");
    
    //edit Visa data
    $randomEditMedNumber = substr(sha1(rand()), 0, 7);
    $editData = array(
      'Condition' => $randomEditMedNumber,
      'Type' => 'Cognitive',
      'Evidence_Note' => 'NA',
    );
    $this->_addMedData($editData, "edit", $random);
 
  }

  function _addMedData($values, $mode = NULL, $condition = NULL) {
    if ($mode == 'add') {
      $this->click("xpath=//a[@title='Medical & Disability']");
      $this->waitForElementPresent("xpath=//form[@id='Edit']/div[2]/a/span");
      $this->click("xpath=//form[@id='Edit']/div[2]/a/span");
    }  	
    else {
      $this->click("xpath=//a[@title='Medical & Disability']");
      $this->waitForElementPresent("xpath=//form[@id='Edit']/div[2]/a/span");
      $this->click("xpath=//div[@id='browseValues']//table/tbody/tr/td[text()='".$condition."']/following-sibling::td[3]/span/a[text()='Edit']");
    }
    $this->waitForElementPresent("xpath=//input[@data-crm-custom='Medical_Disability:Condition']");
    $this->type("xpath=//input[@data-crm-custom='Medical_Disability:Condition']", $values['Condition']);
    $this->select("xpath=//select[@data-crm-custom='Medical_Disability:Type']", "label=".$values["Type"]);
    $this->click("xpath=//label[text() = 'Large Screen']/preceding-sibling::input[1]");
    $this->type("xpath=//textarea[@data-crm-custom='Medical_Disability:Evidence_Note']", $values['Evidence_Note']);
    $this->click("xpath=//input[@id='_qf_Edit_upload']");
    $this->waitForPageToLoad($this->getTimeoutMsec());
    $this->waitForElementPresent("xpath=//li[@aria-controls='Medical__Disability']");
    sleep(2);
    $this->assertTrue($this->isTextPresent($values['Condition']), 'Condition not found after '.$mode.'ing Medical & Disability (_addMedData).');
    $this->click("xpath=//div[@id='browseValues']//table/tbody/tr/td[text()='".$values['Condition']."']/following-sibling::td[3]/span/a[text()='View']");
    $this->assertTrue($this->isTextPresent($values['Condition']), 'Condition not found after '.$mode.'ing Medical & Disability (_addMedData).');
    $this->waitForElementPresent("xpath=//div[8]/div[1]/a");
    $this->click("xpath=//div[8]/div[1]/a");
  }

}

