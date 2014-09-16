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
class WebTest_HRVisa_HRVisaAddEditTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testVisaCreateEdit() {
    $this->webtestLogin();

    // Adding contacts
    $random = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($random, "Jameson", "$random@jameson.name");

    // Check if Immigration tab exists
    $this->assertTrue($this->isElementPresent("xpath=//a[@title='Immigration']"), 'Immigration tab not appearing');

    //add Visa data
    $addData = array(
      'Visa_Type' => 'Visit',
      'Visa_Number' => $random,
      'Start_Date' => date("Y-m-d"),
      'End_Date' => date("Y-m-d",strtotime("+1 month")),
      'Conditions' => 'NA',
      'Evidence' => 'NA',
      'Sponsor_s_Certificate_number' => $random,
    );
    $this->_addVisaData($addData, "add");

    //edit Visa data
    $randomEditVisaNumber = substr(sha1(rand()), 0, 7);
    $editData = array(
      'Visa_Type' => 'Visit Edit',
      'Visa_Number' => $randomEditVisaNumber,
      'Start_Date' => date("Y-m-d"),
      'End_Date' => date("Y-m-d",strtotime("+1 month")),
      'Conditions' => 'NA',
      'Evidence' => 'NA',
      'Sponsor_s_Certificate_number' => $randomEditVisaNumber,
    );
    $this->_addVisaData($editData, "edit", $random);
  }

  function _addVisaData($values, $mode = NULL, $visaNumber = NULL) {
    if ($mode == 'add') {
      $this->click("xpath=//a[@title='Immigration']");
      $this->waitForElementPresent("xpath=//div[@id='mainTabContainer']/div[@class='ui-tabs-panel ui-widget-content ui-corner-bottom crm-ajax-container']/a/span/div");
      $this->click("xpath=//div[@id='mainTabContainer']/div[@class='ui-tabs-panel ui-widget-content ui-corner-bottom crm-ajax-container']/a/span/div");
    }
    else {
      $this->click("xpath=//a[@title='Immigration']");
      $this->waitForElementPresent("xpath=//div[@id='mainTabContainer']/div[@class='ui-tabs-panel ui-widget-content ui-corner-bottom crm-ajax-container']/a/span/div");
      if ($this->isElementPresent("xpath=//div[@id='custom-12-table-wrapper']//table/tbody/tr/td[text()='".$visaNumber."']/following-sibling::td[6]/span/a[text()='Edit']")) {
        $this->click("xpath=//div[@id='custom-12-table-wrapper']//table/tbody/tr/td[text()='".$visaNumber."']/following-sibling::td[6]/span/a[text()='Edit']");
      }
      else {
        $this->click("xpath=//div[@id='custom-12-table-wrapper']//table/tbody/tr/td[text()='".$visaNumber."']/following-sibling::td[5]/span/a[text()='Edit']");
      }
    }
    $this->waitForElementPresent("xpath=//input[@data-crm-custom='Immigration:Visa_Type']");
    $this->type("xpath=//input[@data-crm-custom='Immigration:Visa_Type']", $values['Visa_Type']);
    $this->type("xpath=//input[@data-crm-custom='Immigration:Visa_Number']", $values['Visa_Number']);
    $this->type("xpath=//input[@data-crm-custom='Immigration:Start_Date']", $values['Start_Date']);
    $this->type("xpath=//input[@data-crm-custom='Immigration:End_Date']", $values['End_Date']);
    $this->type("xpath=//textarea[@data-crm-custom='Immigration:Conditions']", $values['Conditions']);
    $this->type("xpath=//textarea[@data-crm-custom='Immigration:Evidence_Note']", $values['Evidence']);
    if ($this->isElementPresent("xpath=//input[@data-crm-custom='Immigration:Sponsor_s_Certificate_number']")) {
      $this->type("xpath=//input[@data-crm-custom='Immigration:Sponsor_s_Certificate_number']", $values['Sponsor_s_Certificate_number']);
    }

    $this->click("xpath=//input[@id='_qf_CustomData_upload']");
    $this->waitForElementPresent("xpath=//li/a[@title='Immigration']");
    sleep(2);
    $this->assertTrue($this->isTextPresent($values['Visa_Number']), 'Visa number not found after '.$mode.'ing visa data (_addVisaData).');
    if ($this->isElementPresent("xpath=//div[@id='custom-12-table-wrapper']//table/tbody/tr/td[text()='".$values['Visa_Number']."']/following-sibling::td[6]/span/a[text()='View']")) {
      $this->click("xpath=//div[@id='custom-12-table-wrapper']//table/tbody/tr/td[text()='".$values['Visa_Number']."']/following-sibling::td[6]/span/a[text()='View']");
    }
    else {
      $this->click("xpath=//div[@id='custom-12-table-wrapper']//table/tbody/tr/td[text()='".$values['Visa_Number']."']/following-sibling::td[5]/span/a[text()='View']");
    }
    $this->assertTrue($this->isTextPresent($values['Visa_Number']), 'Visa number not found after '.$mode.'ing vida data (_addVisaData).');

    // WAS: xpath=//div[8]/div[1]/a
    $close = "xpath=//button[contains(concat(' ',normalize-space(@class),' '),' ui-dialog-titlebar-close ')]";

    $this->waitForElementPresent($close);
    $this->click($close);
  }
}
