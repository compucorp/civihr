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
      $this->waitForElementPresent("xpath=//*[@id='ui-id-13']/div/div[1]/a/span/div");
      $this->click("xpath=//*[@id='ui-id-13']/div/div[1]/a/span/div");
      $this->waitForElementPresent('related_contact_id');
      $this->select2('related_contact_id', $values['contact_1'], TRUE);
      $this->waitForElementPresent("_qf_Relationship_upload-bottom");
      $this->click("_qf_Relationship_upload-bottom");
      sleep(3);
      $this->waitForText('crm-notification-container', "Saved");
    }
  }
}
