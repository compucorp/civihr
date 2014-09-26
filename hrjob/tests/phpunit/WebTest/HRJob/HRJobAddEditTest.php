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
class WebTest_HRJob_HRJobAddEditTest extends CiviSeleniumTestCase {

  protected function setUp() {
    parent::setUp();
  }

  function testJobCreateEdit() {
    $this->webtestLogin();
    $config = CRM_Core_Config::singleton();

    $this->rest_civicrm_api('ContactType','create',array('parent_id'=>3,'name'=>'Health_Insurance_Provider'));
    $this->rest_civicrm_api('ContactType','create',array('parent_id'=>3,'name'=>'Life_Insurance_Provider'));

    $orgName1 = substr(sha1(rand()), 0, 7);
    $org1 = $this->webtestAddOrganization($orgName1, "$orgName1@org.name", 'Health_Insurance_Provider');
    $orgName2 = substr(sha1(rand()), 0, 7);
    $org2 = $this->webtestAddOrganization($orgName2, "$orgName2@org.name", 'Life_Insurance_Provider');
    $orgName3 = substr(sha1(rand()), 0, 7);
    $org3 = $this->webtestAddOrganization($orgName3, "$orgName3@org.name");
    // Adding contacts
    //manager contact
    $firstName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($firstName, "Jameson", "$firstName@jameson.name");

    $contactName = substr(sha1(rand()), 0, 7);
    $this->webtestAddContact($contactName, "Anderson", "$contactName@anderson.name");

    $note = substr(sha1(rand()), 0, 7);

    //add Job data
    $this->_addJobData(array(
      'position' => 'Chief Executive',
      'title' => 'Jr Manager',
      'contract_type' => 'Employee - Temporary',
      'location' => 'Headquarters',
      'notice_amount' => 5,
      'notice_unit' => 'Week'
    ), TRUE);

    $this->_addJobHourData(1, array(
      'hours_type' => 8,
      'hours_unit' => 'Day',
    ));

    $this->_addJobPayData(1, array(
      'pay_scale' => 'NJC pay scale',
      'pay_amount' => 40,
      'pay_unit' => 'Day',
    ));

    $roleValues = array(
      array(
        'title' => 'Manager',
        'description' => 'A test Description',
        'cost_center' => 001,
        'funder1' => $orgName2,
        'funder2' => $orgName3,
        'percent_pay' => 10,
        'department' => 'Finance',
        'level_type' => 'Senior Staff',
        'location' => 'Headquarters',
        'manager_contact_id' => $firstName,
      ),
      array(
        'title' => 'Jr Manager',
        'description' => 'A test Description',
        'cost_center' => 002,
        'funder1' => $orgName1,
        'funder2' => $orgName3,
        'percent_pay' => 5,
        'department' => 'Finance',
        'level_type' => 'Senior Manager',
        'location' => 'Headquarters',
        'manager_contact_id' => $firstName,
      ),
    );
    //add multiple roles
    $this->_addJobRoleData($roleValues[0], 1, 2);
    $this->_addJobRoleData($roleValues[1], 1, 3);

    $this->_addFundingData(1, array(
      'funding_notes' => 'Test Notes'
    ));

    $this->_addJobLeaveData(1, array(
      '1' => 8,
      '2' => 9,
      '3' => 10,
    ));

    $this->_addHealthCareData(1, array(
      'provider' => $orgName1,
      'plan_type' => 'Family',
      'description' => 'This is a Test Description',
      'dependents' => 'Mr X',
      'provider_life_insurance' => $orgName2,
      'plan_type_life_insurance' => 'Individual',
      'description_life_insurance' => 'This is a life insurance description',
      'dependents_life_insurance' => 'Own',
      ));

    $this->_addJobPensionData(1, array(
      'is_enrolled' => 0,
      'pension_type' => 'Employer Pension',
      'er_contrib_pct' => 65,
      'ee_contrib_pct' => 15,
      'ee_contrib_abs' => 10.10,
      'ee_evidence_note' => $note,
    ));

    //add another Job data for the same contact
    $this->_addJobData(array(
      'position' => 'Administrator',
      'title' => 'Sr Admin',
      'contract_type' => 'Contractor',
      'location' => 'Headquarters',
      'notice_amount' => 1,
      'notice_unit' => 'Month'
    ));

    //edit Job Data
    $this->_addJobData(array(
      'position' => 'Volunteer',
      'title' => 'Sr Volunteer',
      'contract_type' => 'Volunteer',
      'location' => 'Headquarters',
      'notice_amount' => 1,
      'notice_unit' => 'Month'
    ), FALSE, 'Edit');

    $this->_addJobHourData(2, array(
      'hours_type' => 8,
      'hours_unit' => 'Day',
    ));

    //edit HoursData
    $this->_addJobHourData(2, array(
      'hours_type' => '4',
      'hours_unit' => 'Week',
    ), 'Edit');

    $this->_addJobPayData(2, array(
      'pay_scale' => 'JNC pay scale',
      'pay_amount' => 60,
      'pay_unit' => 'Day',
    ));

    //edit PayData
    $this->_addJobPayData(2, array(
      'pay_scale' => 'Soulbury Pay Agreement',
      'pay_amount' => 120,
      'pay_unit' => 'Week',
    ), 'Edit');

    $roleValues = array(
      'title' => 'Sr Manager',
      'description' => 'Again a test Description',
      'cost_center' => 003,
      'funder1' => $orgName3,
      'funder2' => $orgName2,
      'percent_pay' => 15,
      'department' => 'HR',
      'level_type' => 'Senior Staff',
      'location' => 'Headquarters',
      'manager_contact_id' => $firstName,
    );
    $this->_addJobRoleData($roleValues, 2, 2);

    //edit role data
    $roleValues = array(
      'title' => 'Project Manager',
      'description' => 'test Description',
      'cost_center' => 005,
      'funder1' => $orgName1,
      'funder2' => $orgName3,
      'percent_pay' => 20,
      'department' => 'Finance',
      'level_type' => 'Senior Staff',
      'location' => 'Headquarters',
      'manager_contact_id' => $firstName,
    );
    $this->_addJobRoleData($roleValues, 2, 2, 'Edit');

    $this->_addFundingData(2, array(
      'funding_notes' => 'Test Funding Notes'
    ));

    $this->_addFundingData(2, array(
     'funding_notes' => 'Test Funding Notes'
    ), 'Edit');

    $this->_addJobLeaveData(2, array(
      '1' => 7,
      '2' => 6,
      '3' => 8,
    ));

    //edit LeaveData
    $this->_addJobLeaveData(2, array(
      '1' => 9,
      '2' => 9,
      '3' => 9,
    ), 'Edit');

    $this->_addHealthCareData(2, array(
      'provider' => $orgName1,
      'plan_type' => 'Individual',
      'description' => 'This is a another Test Description',
      'dependents' => 'Mr Y',
      'provider_life_insurance'=> $orgName2,
      'plan_type_life_insurance'=> 'Family',
      'description_life_insurance'=> 'This is a life insurance description',
      'dependents_life_insurance'=> 'Spouse, children',
    ));

    //edit healthCare data
    $this->_addHealthCareData(2, array(
      'provider' => $orgName1,
      'plan_type' => 'Family',
      'description' => 'A Test Description',
      'dependents' => 'Mr XYZ',
      'provider_life_insurance'=> $orgName2,
      'plan_type_life_insurance'=> 'Family',
      'description_life_insurance'=> 'This is a life insurance description',
      'dependents_life_insurance'=> 'Spouse, children',
      ), 'Edit');

    $this->_addJobPensionData(2, array(
      'is_enrolled' => 0,
      'pension_type' => 'Personal Pension',
      'er_contrib_pct' => 65,
      'ee_contrib_pct' => 15,
      'ee_contrib_abs' => 12.00,
      'ee_evidence_note' => $note,
    ));

    //edit Pension Data
    $this->_addJobPensionData(2, array(
      'is_enrolled' => 1,
      'pension_type' => 'Personal Pension',
      'er_contrib_pct' => 35,
      'ee_contrib_pct' => 5,
      'ee_contrib_abs' => 12.00,
      'ee_evidence_note' => $note,
    ), 'Edit');
  }

  function _addJobData($values, $new = FALSE, $mode = NULL) {
    $this->click("xpath=//a[@title='Jobs']");

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
    $this->select('hrjob-location', "value={$values['location']}");
    $this->type('hrjob-notice_amount', $values['notice_amount']);
    $this->select('hrjob-notice_unit', "value={$values['notice_unit']}");
    $this->click("xpath=//button[@class='crm-button standard-save']");
    sleep(1);

    //assert the saved values
    $this->assertSavedValues($values, array('contract_type', 'location', 'notice_unit'));
  }

  function _addFundingData($jobIndex, $values, $mode = NULL) {
  	if ($mode != 'Edit') {
  	  $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[5]/a");
  	  $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[5]/a");
  	}
    $this->waitForElementPresent('hrjob-funding_notes');
    $this->type('hrjob-funding_notes', $values['funding_notes']);
    $this->click("xpath=//button[@class='crm-button standard-save']");
    sleep(3);
    $this->waitForText('crm-notification-container', "Saved");

  	//assert the saved values
  	$this->assertEquals($values['funding_notes'], $this->getValue("hrjob-funding_notes"));
  }

  function _addJobHourData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[2]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[2]/a");
    }
    $this->waitForElementPresent("hrjob-hours_type");
    $this->select('hrjob-hours_type', "value={$values['hours_type']}");
    $this->select('hrjob-hours_unit', "value={$values['hours_unit']}");
    $this->click("xpath=//button[@class='crm-button standard-save']");
    sleep(2);

    $this->waitForText('crm-notification-container', "Saved");

    //assert the saved values
    $this->assertSavedValues($values, array('hours_type', 'hours_unit'));
  }

  function _addHealthCareData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[7]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[7]/a");
    }
    $this->waitForElementPresent('hrjob-provider');
    sleep(1);
    $this->select2('hrjob-provider', $values['provider']);
    $this->select('hrjob-plan_type', "value={$values['plan_type']}");
    $this->type('hrjob-description', $values['description']);
    $this->type('hrjob-dependents', $values['dependents']);
    $this->waitForElementPresent('hrjob-provider_life_insurance');
    sleep(1);
    $this->select2('hrjob-provider_life_insurance', $values['provider_life_insurance']);
    $this->select('hrjob-plan_type_life_insurance', "value={$values['plan_type_life_insurance']}");
    $this->type('hrjob-description_life_insurance', $values['description_life_insurance']);
    $this->type('hrjob-dependents_life_insurance', $values['dependents_life_insurance']);
    $this->click("xpath=//button[@class='crm-button standard-save']");
    sleep(1);
    $this->waitForText('crm-notification-container', "Saved");
    unset($values['provider']);
    unset($values['provider_life_insurance']);

    //assert the saved values
    $this->assertSavedValues($values, array('plan_type'));
  }

  function _addJobLeaveData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[6]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[6]/a");
    }
    $tbodyXPath = "xpath=//table[@class='hrjob-leave-table']/tbody";
    sleep(2);

    $this->waitForElementPresent("{$tbodyXPath}/tr[3]/td[2]/input");
    $this->type("{$tbodyXPath}/tr[1]/td[2]/input", $values['1']);
    $this->type("{$tbodyXPath}/tr[2]/td[2]/input", $values['2']);
    $this->type("{$tbodyXPath}/tr[3]/td[2]/input", $values['3']);
    $this->click("xpath=//button[@class='crm-button standard-save']");
    sleep(2);
    $this->waitForText('crm-notification-container', "Saved");
    $i = 1;
    foreach ($values as $key => $value) {
      $this->assertEquals($value, $this->getValue("$tbodyXPath/tr[$i]/td[2]/input"));
      $i++;
    }
    sleep(2);
  }

  function _addJobPayData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[3]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[3]/a");
    }
    $this->waitForElementPresent("xpath=//input[@value='1']");
    $this->click("xpath=//input[@value='1']");
    $this->waitForElementPresent("hrjob-pay_scale");
    $this->select('hrjob-pay_scale', "value={$values['pay_scale']}");
    $this->type('hrjob-pay_amount', $values['pay_amount']);
    $this->select('hrjob-pay_unit', "value={$values['pay_unit']}");
    $this->click("xpath=//button[@class='crm-button standard-save']");
    sleep(1);
    $this->waitForText('crm-notification-container', "Saved");
    sleep(1);
    //assert the saved values
    $this->assertSavedValues($values, array('pay_scale', 'pay_unit'));
  }

  function _addJobPensionData($jobIndex, $values, $mode = NULL) {
    if ($mode != 'Edit') {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[8]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[8]/a");
    }

    $this->waitForElementPresent("hrjob-pension_type");
    $this->select('pension_type', $values['pension_type']);

    $this->waitForElementPresent("hrjob-er_contrib_pct");
    $this->select('hrjob-is_enrolled', "value={$values['is_enrolled']}");
    $this->type('hrjob-er_contrib_pct', $values['er_contrib_pct']);

    $this->waitForElementPresent("hrjob-ee_contrib_pct");
    $this->select('hrjob-is_enrolled', "value={$values['is_enrolled']}");
    $this->type('hrjob-ee_contrib_pct', $values['ee_contrib_pct']);

    $this->waitForElementPresent("hrjob-ee_contrib_abs");
    $this->select('hrjob-is_enrolled', "value={$values['is_enrolled']}");
    $this->type('hrjob-ee_contrib_abs', $values['ee_contrib_abs']);

    $this->type('hrjob-ee_evidence_note', $values['ee_evidence_note']);

    $this->click("xpath=//button[@class='crm-button standard-save']");
    sleep(1);
    $this->waitForText('crm-notification-container', "Saved");
    sleep(1);

    //FIXME unsetting "is_enrolled" for now as its default value is not getting set in the screen.
    unset($values['is_enrolled']);
    //assert the saved values
    $this->assertSavedValues($values);
  }

  function _addJobRoleData($values, $jobIndex, $row, $mode = NULL) {
    if ($row == 2) {
      $this->waitForElementPresent("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[4]/a");
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[4]/a");
      $this->waitForElementPresent("xpath=//a[@class='hrjob-role-add']");
      sleep(2);
    }
    if ($mode != 'Edit') {
      $this->click("xpath=//a[@class='hrjob-role-add']");
    }
    else {
      $this->click("xpath=//div[@class='hrjob-tree-items']/div[$jobIndex]/dl/dd[4]/a");
      sleep(2);
      $this->waitForElementPresent("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td/span/span[1]");
      $this->click("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td/span/span[1]");
    }

    $this->waitForElementPresent("xpath=//select[@id='hrjob-region']");

    foreach ($values as $key => $value) {
      if ($key == 'description') {
        $this->type("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div//div[2]/textarea[@id='hrjob-{$key}']", $value);
      }
      elseif ($key == 'location' || $key == 'department' || $key == 'level_type') {
        $this->select("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div//div[2]/select[@id='hrjob-{$key}']","value={$value}");
      }
      elseif ($key == 'manager_contact_id') {
        $this->waitForElementPresent("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div//div[2]/div[@id='s2id_hrjob-manager_contact_id']/a");
        $this->select2("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div//div[2]/div[@id='s2id_hrjob-manager_contact_id']/a", $value, FALSE, TRUE);
      }
      elseif ($key == 'funder1') {
        sleep(1);
        $this->waitForElementPresent("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div[@class='crm-summary-row multi-funder']/div[@class='crm-content']/table[@class='hrjob-role-funder-table']/tbody/tr[1]/td/div/a");
        $this->select2("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div[@class='crm-summary-row multi-funder']/div[@class='crm-content']/table[@class='hrjob-role-funder-table']/tbody/tr[1]/td/div/a", $value, FALSE, TRUE);
        sleep(1);
      }
      elseif ($key == 'funder2') {
        if ($mode != 'Edit') {
          $this->click("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div[@class='crm-summary-row multi-funder']/div[@class='crm-content']/a[@class='hrjob-role-funder-add']");
        }
        $this->select2("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div[@class='crm-summary-row multi-funder']/div[@class='crm-content']/table[@class='hrjob-role-funder-table']/tbody/tr[2]/td/div/a", $value, FALSE, TRUE);
        sleep(1);
      }
      elseif($key == 'percent_pay'){
        $this->type("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div[@class='crm-summary-row multi-funder']/div[@class='crm-content']/table[@class='hrjob-role-funder-table']/tbody/tr[1]/td[2]/input", $value);
        $this->type("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div[@class='crm-summary-row multi-funder']/div[@class='crm-content']/table[@class='hrjob-role-funder-table']/tbody/tr[2]/td[2]/input", $value);
      }
      else {
        $this->type("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div//div[2]/input[@id='hrjob-{$key}']", $value);
      }
    }

    $this->click("xpath=//button[@class='crm-button standard-save']");
    $this->waitForText('crm-notification-container', "Saved");
    sleep(2);

    $this->waitForElementPresent("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/strong[contains(text(),'{$values['title']}')]");
    $this->waitForElementPresent("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td/span/span[1]");
    $this->click("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td/span/span[1]");
    sleep(2);

    $this->waitForElementPresent("xpath=//select[@id='hrjob-region']");

    //assert the saved values for multiple roles
    foreach ($values as $key => $value) {
      if ($key == 'description') {
        $this->assertEquals($value, $this->getValue("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div//div[2]/textarea[@id='hrjob-{$key}']"));

      }
      elseif ($key == 'location' || $key == 'department' || $key == 'level_type') {
        $this->assertEquals($value, $this->getSelectedValue("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div//div[2]/select[@id='hrjob-{$key}']"));
      }
      elseif ($key == 'manager_contact_id') {
        $this->assertElementContainsText("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div[13]/div[1]/label", 'Manager');
      }
      elseif ($key == 'funder1' || $key == 'funder2') {
        $this->assertElementContainsText("xpath=//table[@class='hrjob-role-funder-table']/thead/tr/td", 'Funder');
      }
      elseif ($key == 'percent_pay') {
        $this->assertEquals($value, $this->getValue("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div[@class='crm-summary-row multi-funder']/div[@class='crm-content']/table[@class='hrjob-role-funder-table']/tbody/tr[1]/td[2]/input"));
      }
      else {
        $this->assertEquals($value, $this->getValue("xpath=//table[@class='hrjob-role-table']/tbody/tr[$row]/td[2]/div/div/div//div[2]/input[@id='hrjob-{$key}']"));
      }
    }
    sleep(2);
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
