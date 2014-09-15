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
    $this->assertTrue($this->isElementPresent("xpath=//a[@title='Medical & Disability']"), 'Medical & Disability tab not appearing');

    //add Medical & Disability data
    $addData = array(
      'Condition' => $random,
      'Type' => 'Cognitive',
      'Evidence_Note' => 'NA',
    );
    $this->_addMedData($addData, "add");

    //edit Medical & Disability data
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
      $this->waitForElementPresent("xpath=//div[@id='mainTabContainer']/div[@class='ui-tabs-panel ui-widget-content ui-corner-bottom crm-ajax-container']/a/span/div");
      $this->click("xpath=//div[@id='mainTabContainer']/div[@class='ui-tabs-panel ui-widget-content ui-corner-bottom crm-ajax-container']/a/span/div");
    }
    else {
      $this->click("xpath=//a[@title='Medical & Disability']");
      $this->waitForElementPresent("xpath=//div[@id='mainTabContainer']/div[@class='ui-tabs-panel ui-widget-content ui-corner-bottom crm-ajax-container']/a/span/div");
      $this->click("xpath=//div[@id='custom-10-table-wrapper']//table/tbody/tr/td[text()='".$condition."']/following-sibling::td[4]/span/a[text()='Edit']");
    }
    $this->waitForElementPresent("xpath=//input[@data-crm-custom='Medical_Disability:Condition']");
    $this->type("xpath=//input[@data-crm-custom='Medical_Disability:Condition']", $values['Condition']);
    $this->select("xpath=//select[@data-crm-custom='Medical_Disability:Type']", "label=".$values["Type"]);
    $this->click("xpath=//label[text() = 'Large Screen']/preceding-sibling::input[1]");
    $this->type("xpath=//textarea[@data-crm-custom='Medical_Disability:Evidence_Note']", $values['Evidence_Note']);
    $this->click("xpath=//input[@id='_qf_CustomData_upload']");
    $this->waitForElementPresent("xpath=//li/a[@title='Medical & Disability']");
    sleep(2);
    $this->assertTrue($this->isTextPresent($values['Condition']), 'Condition not found after '.$mode.'ing Medical & Disability (_addMedData).');
    $this->click("xpath=//div[@id='custom-10-table-wrapper']//table/tbody/tr/td[text()='".$values['Condition']."']/following-sibling::td[4]/span/a[text()='View']");
    $this->assertTrue($this->isTextPresent($values['Condition']), 'Condition not found after '.$mode.'ing Medical & Disability (_addMedData).');

    // WAS: xpath=//div[8]/div[1]/a
    $close = "xpath=//button[contains(concat(' ',normalize-space(@class),' '),' ui-dialog-titlebar-close ')]";
    $this->waitForElementPresent($close);
    $this->click($close);
  }

}
