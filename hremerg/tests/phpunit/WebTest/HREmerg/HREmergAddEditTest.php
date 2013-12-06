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
class WebTest_HREmerg_HREmergAddEditTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testEmergCreateEdit() {
    $this->webtestLogin();
    $config = CRM_Core_Config::singleton();
    
    // Adding contacts
    $contactName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($contactName, "Anderson", "$contactName@anderson.name");
    $contact = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($contact, "Adams", "$contact@adams.name");

    //add Job data
    $this->_addEmergData(array(
      'contact_1' => $contactName
    ), TRUE);
  }
  function _addEmergData($values, $new = FALSE, $mode = NULL) {
    if ($new) {
      $this->click("xpath=//a[@title='Emergency Contacts']");
      $this->waitForElementPresent("xpath=//div[@id='Emergency_Contacts']/div/div/div/div/a/span");
      $this->click("xpath=//div[@id='Emergency_Contacts']/div/div/div/div/a/span");
      $this->waitForElementPresent("xpath=//form[@id='Relationship']/div[2]/div/table/tbody/tr[2]/td[2]/input[@class='form-text ac_input']");
      $this->type("xpath=//form[@id='Relationship']/div[2]/div/table/tbody/tr[2]/td[2]/input[@class='form-text ac_input']",$values['contact_1'] );
      $this->click("xpath=//form[@id='Relationship']/div[2]/div/table/tbody/tr[2]/td[2]/input[@class='form-text ac_input']");
      $this->waitForElementPresent("css=div.ac_results-inner li");
      $this->click("css=div.ac_results-inner li");
      $this->waitForElementPresent("xpath=//div[@id='saveDetails']/span[1]/input");
      $this->click("xpath=//div[@id='saveDetails']/span[1]/input");
      sleep(3);
      $this->waitForText('crm-notification-container', "Saved");
    }
  }
}