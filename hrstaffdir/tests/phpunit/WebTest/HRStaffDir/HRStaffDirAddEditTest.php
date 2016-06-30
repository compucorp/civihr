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

class WebTest_HRStaffDir_HRStaffDirAddEditTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testMedCreateEdit() {
    $this->webtestLogin();
    $this->openCiviPage("dashboard", "reset=1");

    // Check if Directory menu item exists
    $this->assertTrue($this->isElementPresent("xpath=//ul[@id='civicrm-menu']/li/a[text()='Directory']"), 'Directory not appearing in the top nav bar');

    $url = $this->getAttribute("xpath=//ul[@id='civicrm-menu']/li/a[text()='Directory']/@href");
    $url = str_replace('/table', '', $url);
    // Adding contacts
    $random = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($random, "Jameson", "$random@jameson.name");
    $this->open($url);
    $this->waitForPageToLoad($this->getTimeoutMsec());
    $this->click("xpath=//form[@id='Search']/div[2]/div[1]/div/div[1]");
    $this->type("xpath=//input[@id='first_name']", $random);
    sleep(1);
    $this->click("_qf_Search_refresh");
    $this->waitForPageToLoad($this->getTimeoutMsec());
    $this->assertFalse($this->isTextPresent("No matches found for"), 'Contact not found.');
    $this->assertTrue($this->isTextPresent($random), 'Contact not found.');
  }


}

