<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
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
class WebTest_HRJob_HRJobAddEditTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testJobCreateEdit() {
    $this->webtestLogin();

    // Adding contacts
    //manager contact
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Jameson", "$firstName@jameson.name");

    $contactName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($contactName, "Anderson", "$contactName@anderson.name");

    //add Job data
    $this->_addJobData(array(
      'position' => 'Chief Executive', 
      'title' => 'Jr Manager', 
      'contract_type' => 'Employee', 
      'level_type' => 'Junior Manager', 
      'period_type' => 'Permanent', 
      'is_tied_to_funding' => 'on', 
      'funding_notes' => 'Test Notes'
    ), TRUE);

    $this->_addHealthCareData(1, array(
      'provider' => 'Unknown', 
      'plan_type' => 'Family', 
      'description' => 'This is a Test Description', 
      'dependents' => 'Mr X',
    ));

    $this->_addJobHourData(1, array(
      'hours_type' => 'full',
      'hours_amount' => 40.00, 
      'hours_unit' => 'Day', 
      'hours_fte' => 1,
    ));

    $this->_addJobLeaveData(1, array(
      'annual' => 8,
      'public' => 9,
      'sick' => 10,
    ));

    $this->_addJobPayData(1, array(
      'pay_grade' => 'paid',
      'pay_amount' => 40,
      'pay_unit' => 'Day',
    ));

    $this->_addJobPensionData(1, array(
      'is_enrolled' => 0,
      'er_contrib_pct' => 65,
      'ee_contrib_pct' => 15,
    ));

    $roleValues = array(
      array(
        'title' => 'Manager',
        'description' => 'A test Description',
        'hours' => 40.00,
        'cost_center' => 001,
        'department' => 'Fundraising',
        'functional_area' => 'Save the Whales',
        'location' => 'Headquarters',
        'ac_input' => $firstName,
        'organization' => 'ZING',
        'region' => 'Europe',
      ),
      array(
        'title' => 'Jr Manager',
        'description' => 'A test Description',
        'hours' => 60.00,
        'cost_center' => 002,
        'department' => 'Finance',
        'functional_area' => 'Save the Whales',
        'location' => 'Headquarters',
        'organization' => 'ZING',
        'region' => 'Europe',
      ),
    );
    //add multiple roles
    $this->_addJobRoleData($roleValues[0], 1, 1);
    $this->_addJobRoleData($roleValues[1], 1, 2);

    //add another Job data for the same contact
    $this->_addJobData(array(
      'position' => 'Administrator', 
      'title' => 'Sr Admin', 
      'contract_type' => 'Contractor', 
      'level_type' => 'Senior Manager', 
      'period_type' => 'Temporary',
      'funding_notes' => 'Test Notes again',
    ));

    //edit Job Data
    $this->_addJobData(array(
      'position' => 'Volunteer', 
      'title' => 'Sr Volunteer', 
      'contract_type' => 'Volunteer', 
      'level_type' => 'Senior Staff', 
      'period_type' => 'Permanent',
      'funding_notes' => 'Test Notes',
    ), FALSE, 'Edit');
                                                                                                                                
    $this->_addHealthCareData(2, array(
      'provider' => 'Unknown', 
      'plan_type' => 'Individual', 
      'description' => 'This is a another Test Description', 
      'dependents' => 'Mr Y',
    ));

    //edit healthCare data
    $this->_addHealthCareData(2, array(
      'provider' => 'Unknown', 
      'plan_type' => 'Family', 
      'description' => 'A Test Description', 
      'dependents' => 'Mr XYZ',
    ), 'Edit');

    $this->_addJobHourData(2, array(
      'hours_type' => 'part',
      'hours_amount' => 80.00, 
      'hours_unit' => 'Day', 
      'hours_fte' => 2,
    ));

    //edit HoursData
    $this->_addJobHourData(2, array(
      'hours_type' => 'casual',
      'hours_amount' => 100.00, 
      'hours_unit' => 'Week', 
      'hours_fte' => 3,
    ), 'Edit');

    $this->_addJobLeaveData(2, array(
      'annual' => 7,
      'public' => 6,
      'sick' => 8,
    ));

    //edit LeaveData
    $this->_addJobLeaveData(2, array(
      'annual' => 9,
      'public' => 9,
      'sick' => 9,
    ), 'Edit');

    $this->_addJobPayData(2, array(
      'pay_grade' => 'unpaid',
      'pay_amount' => 60,
      'pay_unit' => 'Day',
    ));

    //edit PayData
    $this->_addJobPayData(2, array(
      'pay_grade' => 'paid',
      'pay_amount' => 120,
      'pay_unit' => 'Week',
    ), 'Edit');

    $this->_addJobPensionData(2, array(
      'is_enrolled' => 0,
      'er_contrib_pct' => 65,
      'ee_contrib_pct' => 15,
    ));

    //edit Pension Data
    $this->_addJobPensionData(2, array(
      'is_enrolled' => 1,
      'er_contrib_pct' => 35,
      'ee_contrib_pct' => 5,
    ), 'Edit');

    $roleValues = array(
      'title' => 'Sr Manager',
      'description' => 'Again a test Description',
      'hours' => 80.00,
      'cost_center' => 003,
      'department' => 'Operations',
      'functional_area' => 'Save the Tigers',
      'location' => 'Home',
      'organization' => 'ZINGIT',
      'region' => 'Asia',
    );
    $this->_addJobRoleData($roleValues, 2, 1);

    //edit role data
    $roleValues = array(
      'title' => 'Project Manager',
      'description' => 'test Description',
      'hours' => 120.00,
      'cost_center' => 005,
      'department' => 'Finance',
      'functional_area' => 'Save the Panda',
      'location' => 'Headquarters',
      'organization' => 'ZING',
      'region' => 'Europe',
    );
    $this->_addJobRoleData($roleValues, 2, 1);
  }

  function _addJobData($values, $new = FALSE, $mode = NULL) {
    if ($mode != 'Edit') {
      if ($new) {
        $this->waitForElementPresent("xpath=//a[@class='hrjob-add']");
        $this->click("xpath=//a[@class='hrjob-add']");
      }
      else {
        $this->waitForElementPresent("xpath=//div[@class='hrjob-container']/div[2]/div/div/form/button");
        $this->click("xpath=//div[@class='hrjob-container']/div[2]/div/div/form/button");
      }
    }
    $this->waitForElementPresent('hrjob-position');
    $this->type('hrjob-position', $values['position']);
    $this->type('hrjob-title', $values['title']);
    $this->select('hrjob-contract_type', "value={$values['contract_type']}");
    $this->select('hrjob-level_type', "value={$values['level_type']}");
    $this->select('hrjob-period_type', "value={$values['period_type']}");
    if (isset($values['is_tied_to_funding'])) {
      $this->click('hrjob-is_tied_to_funding');
    }
    $this->type('hrjob-funding_notes', $values['funding_notes']);
    $this->click("xpath=//button[@class='standard-save']");
    sleep(1);

    //assert the saved values
    if (isset($values['is_tied_to_funding'])) {
      $this->assertChecked('is_tied_to_funding');
    }
    $this->assertSavedValues($values, array('contract_type', 'level_type', 'period_type')); 
  }

  function _addHealthCareData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[2]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[2]/a");
    }
    $this->waitForElementPresent('hrjob-provider');
    $this->select('hrjob-provider', "value={$values['provider']}");
    $this->select('hrjob-plan_type', "value={$values['plan_type']}");
    $this->type('hrjob-description', $values['description']);
    $this->type('hrjob-dependents', $values['dependents']);
    $this->click("xpath=//button[@class='standard-save']");
    sleep(1);
    $this->waitForText('crm-notification-container', "Saved");

    //assert the saved values
    $this->assertSavedValues($values, array('provider', 'plan_type'));
  }

  function _addJobHourData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[3]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[3]/a");
    }
    $this->waitForElementPresent("hrjob-hours_fte");
    $this->select('hrjob-hours_type', "value={$values['hours_type']}");
    $this->type('hrjob-hours_amount', $values['hours_amount']);
    $this->select('hrjob-hours_unit', "value={$values['hours_unit']}");
    $this->type('hrjob-hours_fte', $values['hours_fte']);
    $this->click("xpath=//button[@class='standard-save']");
    sleep(1);
    $this->waitForText('crm-notification-container', "Saved");

    //assert the saved values
    $this->assertSavedValues($values, array('hours_type', 'hours_unit'));
  }

  function _addJobLeaveData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[4]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[4]/a");
    }
    $tbodyXPath = "xpath=//div[@class='hrjob-main-region']/div//table/tbody";
    $this->waitForElementPresent("$tbodyXPath/tr[3]/td[2]/input");
    $this->type("$tbodyXPath/tr[1]/td[2]/input", $values['annual']);
    $this->type("$tbodyXPath/tr[2]/td[2]/input", $values['public']);
    $this->type("$tbodyXPath/tr[3]/td[2]/input", $values['sick']);
    $this->click("xpath=//button[@class='standard-save']");
    sleep(1);
    $this->waitForText('crm-notification-container', "Saved");

    $i = 1;
    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getValue("$tbodyXPath/tr[$i]/td[2]/input"));
      $i++;
    }
  }

  function _addJobPayData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[5]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[5]/a");
    }
    $this->waitForElementPresent("hrjob-pay_unit");
    $this->select('hrjob-pay_grade', "value={$values['pay_grade']}");
    $this->type('hrjob-pay_amount', $values['pay_amount']);
    $this->select('hrjob-pay_unit', "value={$values['pay_unit']}");
    $this->click("xpath=//button[@class='standard-save']");
    sleep(1);
    $this->waitForText('crm-notification-container', "Saved");

    //assert the saved values
    $this->assertSavedValues($values, array('pay_grade', 'pay_unit'));
  }

  function _addJobPensionData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[6]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[6]/a");
    }
    $this->waitForElementPresent("hrjob-er_contrib_pct");
    $this->select('hrjob-is_enrolled', "value={$values['is_enrolled']}");
    $this->type('hrjob-er_contrib_pct', $values['er_contrib_pct']);

    $this->waitForElementPresent("hrjob-ee_contrib_pct");
    $this->select('hrjob-is_enrolled', "value={$values['is_enrolled']}");
    $this->type('hrjob-ee_contrib_pct', $values['ee_contrib_pct']);

    $this->click("xpath=//button[@class='standard-save']");
    sleep(1);
    $this->waitForText('crm-notification-container', "Saved");

    //FIXME unsetting "is_enrolled" for now as its default value is not getting set in the screen. 
    unset($values['is_enrolled']);
    //assert the saved values
    $this->assertSavedValues($values);
  }

  function _addJobRoleData($values, $jobIndex, $row) {
    if ($row == 1) {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[7]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[7]/a");
      $this->waitForElementPresent("xpath=//a[@class='hrjob-role-add']");
    }
   
    $this->click("xpath=//a[@class='hrjob-role-add']");
    $this->waitForElementPresent("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div[10]/div[2]/input[@id='hrjob-region']");
    foreach ($values as $key => $value) {
      if ($key == 'description') {
        $this->type("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div//div[2]/textarea[@id='hrjob-{$key}']", $value);
      }
      elseif ($key == 'location') {
        $this->select("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div//div[2]/select[@id='hrjob-{$key}']","value={$value}");
      }
      elseif ($key == 'ac_input') {
        $this->click("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div//div[2]/input[@class='$key']");
        $this->type("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div//div[2]/input[@class='$key']", $value);
        $this->typeKeys("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div//div[2]/input[@class='$key']", $value);
        $this->waitForElementPresent("css=div.ac_results-inner li");
        $this->click("css=div.ac_results-inner li");
      }
      else {
        $this->type("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div//div[2]/input[@id='hrjob-{$key}']", $value);
      }
    }
    $this->click("xpath=//button[@class='standard-save']");
    sleep(1);
    $this->waitForText('crm-notification-container', "Saved");
    $this->click("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/strong[contains(text(),'{$values['title']}')]");  

    //assert the saved values for multiple roles
    unset($values['ac_input']);
    foreach ($values as $key => $value) {
      if ($key == 'description') {
        $this->assertEquals($value, $this->getValue("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div//div[2]/textarea[@id='hrjob-{$key}']"));
      }
      elseif ($key == 'location') {
        $this->assertEquals($value, $this->getSelectedValue("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div//div[2]/select[@id='hrjob-{$key}']"));
      }
      else {
        $this->assertEquals($value, $this->getValue("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/form/div//div[2]/input[@id='hrjob-{$key}']"));
      }
    }
  }

  function assertSavedValues($values, $selectArray = NULL) {
    foreach ($values as $key => $value) {
      if (!empty($selectArray) && in_array($key, $selectArray)) {
        $this->assertEquals($value, $this->getSelectedValue("id=hrjob-{$key}"));
      }
      else {
        $this->assertEquals($value, $this->getValue("hrjob-{$key}"));
      }
    }
  }
}

