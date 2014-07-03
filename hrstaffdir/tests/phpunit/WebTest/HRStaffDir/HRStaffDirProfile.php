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
class WebTest_HRStaffDir_HRStaffDirProfile extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testMedCreateEdit() {
    $this->webtestLogin();
    $this->openCiviPage("dashboard", "reset=1");
    
    // Check if Directory menu item exists
    $this->assertTrue($this->isElementPresent("xpath=//ul[@id='civicrm-menu']/li/a[text()='Directory']"), 'Directory not appearing in the top nav bar');
        
    // Adding contacts
    $random = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($random, "Jameson", "$random@jameson.name");
    
    // Open directory listing
    $this->click("xpath=//ul[@id='civicrm-menu']/li/a[text()='Directory']");
    $this->waitForPageToLoad($this->getTimeoutMsec());

    // Type text to search
    $this->click("xpath=//div[@class='dataTables_wrapper']/table/thead/tr/th[3]");
    $this->click("xpath=//div[@class='dataTables_filter']/label/input[@type='text']");
    $this->type("xpath=//div[@class='dataTables_filter']/label/input", $random);
    $this->typeKeys("xpath=//div[@class='dataTables_filter']/label/input", $random);
    sleep(1);
  }


}

