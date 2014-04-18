<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.3                                                 |
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


require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';
class CRM_HRRecruitment_BAO_HRVacancyTest extends CiviUnitTestCase {

  function setUp() {
    parent::setUp();
  }

  function teardown() {
  }

  protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    if (!parent::_populateDB($perClass, $object)) {
      return FALSE;
    }

    //populate vacancy_status and case_status of type Application
    $result = civicrm_api3('OptionGroup', 'create', array(
      'name' => 'vacancy_status',
      'title' => ts('Vacancy Status'),
      'is_reserved' => 1,
      'is_active' => 1,
      )
    );
    $vacancyStatus = array(
      'Draft' => ts('Draft'),
      'Open' => ts('Open'),
      'Closed' => ts('Closed'),
      'Cancelled' => ts('Cancelled'),
      'Rejected' => ts('Rejected')
    );
    $weight = 1;
    foreach ($vacancyStatus as $name => $label) {
      $statusParam = array(
        'option_group_id' => $result['id'],
        'label' => $label,
        'name' => $name,
        'value' => $weight++,
        'is_active' => 1,
      );
      if ($name == 'Draft') {
        $statusParam['is_default'] = 1;
      }
      elseif ($name == 'Open') {
        $statusParam['is_reserved'] = 1;
      }
      civicrm_api3('OptionValue', 'create', $statusParam);
    }

    $stages = array(
      'Apply' => ts('Apply'),
      'Ongoing_Vacancy' => ts('Ongoing'),
      'Phone_Interview' => ts('Phone Interview'),
      'Manager_Interview' => ts('Manager Interview'),
      'Board_Interview' => ts('Board Interview'),
      'Group_Interview' => ts('Group Interview'),
      'Psych_Exam' => ts('Psych Exam'),
      'Offer' => ts('Offer'),
      'Hired' => ts('Hired'),
    );
    $count = count(CRM_Core_OptionGroup::values('case_status'));
    foreach ($stages as $name => $label) {
      $count++;

      $caseStatusParam = array(
        'option_group_id' => 'case_status',
        'label' => $label,
        'name' => $name,
        'value' => $count,
        'grouping' => 'Vacancy',
        'filter' => 1,
      );
      civicrm_api3('OptionValue', 'create', $caseStatusParam);
    }
    return TRUE;
  }

  /**
   * create() method (create and update modes)
   */
  function testCreateGet() {

    $permissionIndividual1Id = Contact::createIndividual();
    $permissionIndividual2Id = Contact::createIndividual();
    $stages = array_keys(CRM_Core_OptionGroup::values('case_status', FALSE, FALSE, FALSE, " AND grouping = 'Vacancy'"));

    $params = array(
      'position' => 'Senior Support Specialist',
      'location' => 'Headquaters',
      'salary' => '$110-$130k/yr',
      'description' => 'Answer phone calls and emails from irate customers.',
      'benefits' => 'Have a place to park',
      'requirements' => 'Pro-actively looks to build cross discipline experience and increase knowledge.',
      'stages' => $stages,
      'application_profile' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'application_profile', 'id', 'name'),
      'evaluation_profile' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'evaluation_profile', 'id', 'name'),
      'status_id' => '4',
      'start_date' => '20140425151100',
      'end_date' => '20140426231100',
      'permission' => array('manage Applicants', 'administer Vacancy'),
      'permission_contact_id' => array($permissionIndividual1Id, $permissionIndividual2Id),
    );

    $vacancy = CRM_HRRecruitment_BAO_HRVacancy::create($params);
    $getValues = array();
    $getParams = array('id' => $vacancy->id);

    CRM_HRRecruitment_BAO_HRVacancy::retrieve($getParams, $getValues);
    //stage array index always starts with 1 so in order to make changes in
    //$getValues['stages'] in order to just match the value
    $getValues['stages'] = array_values($getValues['stages']);
    $getValues['permission'] = array_values($getValues['permission']);
    $getValues['permission_contact_id'] = array_values($getValues['permission_contact_id']);

    //process date back to mysql format
    $getValues['start_date'] = CRM_Utils_Date::isoToMysql($getValues['start_date']);
    $getValues['end_date'] = CRM_Utils_Date::isoToMysql($getValues['end_date']);

    foreach ($params as $column => $value) {
      $this->assertEquals($params[$column], $getValues[$column], "Check for Job $column.");
    }
  }

}


