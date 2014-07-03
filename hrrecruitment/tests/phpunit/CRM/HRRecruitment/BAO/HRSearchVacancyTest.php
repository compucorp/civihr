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

require_once 'CiviTest/CiviUnitTestCase.php';
require_once 'CiviTest/Contact.php';
class CRM_HRRecruitment_BAO_HRSearchVacancyTest extends CiviUnitTestCase {

  function setUp() {
    parent::setUp();
    $permissionIndividual1Id = Contact::createIndividual();
    $permissionIndividual2Id = Contact::createIndividual();
    $juniorposition = 'Junior Support Specialist ' . substr(sha1(rand()), 0, 7);
    $this->juniorParams = array(
      'position' => $juniorposition,
      'location' => 'Headquaters',
      'salary' => '$110-$130k/yr',
      'description' => 'Answer phone calls and emails from irate customers.',
      'benefits' => 'Have a place to park',
      'requirements' => 'Pro-actively looks to build cross discipline experience and increase knowledge.',
      'application_profile' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'application_profile', 'id', 'name'),
      'evaluation_profile' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'evaluation_profile', 'id', 'name'),
      'status_id' => '1',
      'start_date' => '20140425151100',
      'end_date' => '20140426231100',
      'permission' => array('manage Applicants', 'administer Vacancy'),
      'permission_contact_id' => array($permissionIndividual1Id, $permissionIndividual2Id),
    );
    $this->juniorPosition = CRM_HRRecruitment_BAO_HRVacancy::create($this->juniorParams);

    $juniorposition2 = 'Junior Support Specialist ' . substr(sha1(rand()), 0, 7);
    $this->juniorParams2 = array(
      'position' => $juniorposition2,
      'location' => 'Home or Home-Office',
      'salary' => '$110-$130k/yr',
      'status_id' => '1',
      'start_date' => '20140425151100',
      'end_date' => '20140426231100',
    );
    $this->juniorPosition2 = CRM_HRRecruitment_BAO_HRVacancy::create($this->juniorParams2);

    $seniorposition = 'Senior Support Specialist ' . substr(sha1(rand()), 0, 7);
    $this->seniorParams = array(
      'position' => $seniorposition,
      'location' => 'Headquaters',
      'salary' => '$110-$130k/yr',
      'description' => 'Answer phone calls and emails from irate customers.',
      'benefits' => 'Have a place to park',
      'requirements' => 'Pro-actively looks to build cross discipline experience and increase knowledge.',
      'application_profile' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'application_profile', 'id', 'name'),
      'evaluation_profile' => CRM_Core_DAO::getFieldValue('CRM_Core_DAO_UFGroup', 'evaluation_profile', 'id', 'name'),
      'status_id' => '2',
      'start_date' => '20140425151100',
      'end_date' => '20140426231100',
      'permission' => array('manage Applicants', 'administer Vacancy'),
      'permission_contact_id' => array($permissionIndividual1Id, $permissionIndividual2Id),
    );
    $this->seniorPosition = CRM_HRRecruitment_BAO_HRVacancy::create($this->seniorParams);
  }

  function teardown() {
  }

  protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    if (!parent::_populateDB($perClass, $object)) {
      return FALSE;
    }
    _hrrecruitment_phpunit_populateDB();
    return TRUE;
  }

  /*
   * search with only loaction
   * success expected.
   */
  function testSearchWithLocation() {
    /*
     * for Location:Headquarters
     */
    $getParams = array('location' => 'Headquaters', 'version' => 3);
    $result = civicrm_api('HRVacancy', 'get', $getParams);

    $seniorPosition = $result['values'][$this->seniorPosition->id];
    $juniorPosition = $result['values'][$this->juniorPosition->id];

    $this->assertEquals($juniorPosition['id'], $this->juniorPosition->id, 'In line ' . __LINE__);
    $this->assertEquals($juniorPosition['location'], 'Headquaters', 'In line ' . __LINE__);
    $this->assertEquals($juniorPosition['position'], $this->juniorParams['position'], 'In line ' . __LINE__);

    $this->assertEquals($seniorPosition['id'], $this->seniorPosition->id, 'In line ' . __LINE__);
    $this->assertEquals($seniorPosition['location'], 'Headquaters', 'In line ' . __LINE__);
    $this->assertEquals($seniorPosition['position'], $this->seniorParams['position'], 'In line ' . __LINE__);

    //all other job position(rather than job specified with location HEADQUATER) should not exists
    $this->assertArrayNotHasKey($this->juniorPosition2->id, $result['values']);
  }

  /*
   * search with only position
   * success expected.
   */
  function testSearchWithPosition() {
    /*
     * for position:junior
     */
    $getParams = array('position' => $this->juniorParams['position'], 'version' => 3);
    $result = civicrm_api('HRVacancy', 'get',$getParams);

    $juniorPosition = $result['values'][$this->juniorPosition->id];

    //asserts for position specified in $this->juniorParams
    $this->assertEquals($juniorPosition['id'], $this->juniorPosition->id, 'In line ' . __LINE__);
    $this->assertEquals($juniorPosition['location'], 'Headquaters', 'In line ' . __LINE__);
    $this->assertEquals($juniorPosition['position'], $this->juniorParams['position'], 'In line ' . __LINE__);

    //all other job position(rather than position specified in $this->juniorParams) should not exists
    $this->assertArrayNotHasKey($this->seniorPosition->id, $result['values']);
    $this->assertArrayNotHasKey($this->juniorPosition2->id, $result['values']);
  }

  /*
   * search with only status
   * success expected.
   */
  function testSearchWithStatus() {
    /*
     * for status:Draft
     */
    $getParams = array('status_id' => 1, 'version' => 3);
    $result = civicrm_api('HRVacancy', 'get',$getParams);

    $juniorPosition = $result['values'][$this->juniorPosition->id];
    $juniorPosition2 = $result['values'][$this->juniorPosition2->id];

    //asserts for status as draft
    $this->assertEquals($juniorPosition['id'], $this->juniorPosition->id, 'In line ' . __LINE__);
    $this->assertEquals($juniorPosition['location'], 'Headquaters', 'In line ' . __LINE__);
    $this->assertEquals($juniorPosition['position'], $this->juniorParams['position'], 'In line ' . __LINE__);

    $this->assertEquals($juniorPosition2['id'], $this->juniorPosition2->id, 'In line ' . __LINE__);
    $this->assertEquals($juniorPosition2['location'], 'Home or Home-Office', 'In line ' . __LINE__);
    $this->assertEquals($juniorPosition2['position'], $this->juniorParams2['position'], 'In line ' . __LINE__);

    //all other job position(rather than job having status draft) should not exists
    $this->assertArrayNotHasKey($this->seniorPosition->id, $result['values']);
  }

  /*
   * search with invalid data
   */
  function testSearchWithWrongData() {
    // for position
    $getParams = array('location' => 'Headquaters' . CRM_Core_DAO::VALUE_SEPARATOR . 'abc', 'version' => 3);
    $result = civicrm_api('HRVacancy', 'get',$getParams);
    $this->assertEquals(empty($result['values']), TRUE, 'In line ' . __LINE__);

    //for status
    $getParams = array('status_id' => '1698', 'version' => 3);
    $result = civicrm_api('HRVacancy', 'get',$getParams);
    $this->assertEquals(empty($result['values']), TRUE, 'In line ' . __LINE__);

    //for position
    $dummyposition = 'Dummy Junior Support ' . substr(sha1(rand()), 0, 7);
    $getParams = array('position' => $dummyposition, 'version' => 3);
    $result = civicrm_api('HRVacancy', 'get',$getParams);
    $this->assertEquals(empty($result['values']), TRUE, 'In line ' . __LINE__);
  }
}


