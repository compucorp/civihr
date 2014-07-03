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
| License and the CiviCRM Licensing Exception along                  |
| with this program; if not, contact CiviCRM LLC                     |
| at info[AT]civicrm[DOT]org. If you have questions about the        |
| GNU Affero General Public License or the licensing of CiviCRM,     |
| see the CiviCRM license FAQ at http://civicrm.org/licensing        |
+--------------------------------------------------------------------+
*/

require_once 'CiviTest/CiviUnitTestCase.php';

/**
 * FIXME
 */
class api_v3_HRJobRoleTest extends CiviUnitTestCase {
  function setUp() {
    // If your test manipulates any SQL tables, then you should truncate
    // them to ensure a consisting starting point for all tests
    $this->quickCleanup(array('civicrm_contact', 'civicrm_hrjob', 'civicrm_hrjob_role'));
    parent::setUp();
  }

  function tearDown() {
    parent::tearDown();
  }

  protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    if (!parent::_populateDB($perClass, $object)) {
      return FALSE;
    }
    _hrjob_phpunit_populateDB();
    return TRUE;
  }

  public function testCreateUpdate() {
    // create an example role
    $role = CRM_Core_DAO::createTestObject('CRM_HRJob_DAO_HRJobRole')->toArray();
    $this->assertTrue(is_numeric($role['id']));
    $this->assertTrue(is_numeric($role['job_id']));
    $this->assertNotEmpty($role['title']);
    $this->assertNotEmpty($role['location']);

    // update the role
    $result = $this->callAPISuccess('HRJobRole', 'create', array(
      'id' => $role['id'],
      'description' => 'new description',
      'location' => '',
    ));

    // check return format
    $this->assertEquals(1, $result['count']);
    foreach ($result['values'] as $roleResult) {
      $this->assertEquals('new description', $roleResult['description']);
      $this->assertEquals('', $roleResult['location']); // BUG: $roleResult['location'] === 'null'
    }
  }

  public function testReplace() {
    // create an example role
    $role = CRM_Core_DAO::createTestObject('CRM_HRJob_DAO_HRJobRole')->toArray();
    $this->assertTrue(is_numeric($role['id']));
    $this->assertTrue(is_numeric($role['job_id']));
    $this->assertNotEmpty($role['title']);
    $this->assertNotEmpty($role['location']);

    // replace the role
    $result = $this->callAPISuccess('HRJobRole', 'replace', array(
      'job_id' => $role['job_id'],
      'values' => array(
        array(
          'id' => $role['id'],
          'job_id' => $role['job_id'],
          'description' => 'new description',
          'location' => '',
        ),
      ),
    ));

    // check return format
    $this->assertEquals(1, $result['count']);
    foreach ($result['values'] as $roleResult) {
      $this->assertTrue(is_array($roleResult));
      // unspecified are preserved & returned
      $this->assertEquals($role['job_id'], $roleResult['job_id']);
      $this->assertEquals($role['title'], $roleResult['title']);

      // passed in values are updated
      $this->assertEquals('new description', $roleResult['description']);
      $this->assertEquals('', $roleResult['location']); // BUG: $roleResult['location'] === 'null'
    }
  }
}
