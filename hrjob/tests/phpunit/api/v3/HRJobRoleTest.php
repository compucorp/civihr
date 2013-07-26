<?php

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

    $import = new CRM_Utils_Migrate_Import();
    $import->run(
      CRM_Extension_System::singleton()->getMapper()->keyToBasePath('org.civicrm.hrjob')
        . '/xml/option_group_install.xml'
    );

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