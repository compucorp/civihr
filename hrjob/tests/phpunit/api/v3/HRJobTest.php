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
class api_v3_HRJobTest extends CiviUnitTestCase {
  function setUp() {
    // If your test manipulates any SQL tables, then you should truncate
    // them to ensure a consisting starting point for all tests
    // $this->quickCleanup(array('example_table_name'));
    parent::setUp();

    $this->fixtures['fullJob'] = array(
      'version' => 3,
      'title' => 'Test Job',
      'contract_type' => 'Volunteer',
      'api.HRJobPay.create' => array(
        'pay_amount' => 20,
      ),
      'api.HRJobHealth.create' => array(
        'plan_type' => 'Family',
        'description' => 'A test Description',
      ),
      'api.HRJobHour.create' => array(
        'hours_type' => 4,
        'hours_amount' => 40.00,
        'hours_unit' => 'Week',
      ),
      'api.HRJobPension.create' => array(
        'is_enrolled' => 1,
        'er_contrib_pct' => 75.00,
        'ee_contrib_pct' => 10.00,
      ),
      'api.HRJobRole.create' => array(
        'title' => 'Manager',
        'region' => 'Asia',
        'department' => 'Finance',
        'location' => 'Headquarters',
        'organization' => 'ABC Inc',
      ),
      'api.HRJobRole.create.1' => array(
        'title' => 'Senior Manager',
        'region' => 'Europe',
        'department' => 'HR',
        'location' => 'Home',
        'organization' => 'XYZ Inc',
      ),
      'api.HRAbsenceType.create' => array(
        'name' => 'Annual',
        'title' => 'Annual',
        'is_active' => 1,
        'allow_credits' => 0,
        'allow_debits' => 0,
      ),
      'api.HRAbsenceType.create.1' => array(
        'name' => 'Public',
        'title' => 'Public',
        'is_active' => 1,
        'allow_credits' => 0,
        'allow_debits' => 0,
      ),
      'api.HRAbsenceType.create.2' => array(
        'name' => 'Sick',
        'title' => 'Sick',
        'is_active' => 1,
        'allow_credits' => 0,
        'allow_debits' => 0,
      ),
      'api.HRJobLeave.create' => array(
        'leave_type' => 1,
        'leave_amount' => 10,
      ),
      'api.HRJobLeave.create.1' => array(
        'leave_type' => 3,
        'leave_amount' => 7
      ),
    );
  }

  function tearDown() {
    parent::tearDown();
    $this->quickCleanup(array(
      'civicrm_hrjob',
      'civicrm_hrabsence_type',
      'civicrm_hrjob_health',
      'civicrm_hrjob_hour',
      'civicrm_hrjob_leave',
      'civicrm_hrjob_pay',
      'civicrm_hrjob_pension',
      'civicrm_hrjob_role',
    ));
  }

  protected static function _populateDB($perClass = FALSE, &$object = NULL) {
    if (!parent::_populateDB($perClass, $object)) {
      return FALSE;
    }
    _hrjob_phpunit_populateDB();
    return TRUE;
  }

  /**
   * Create a job and several subordinate entities using API chaining
   */
  function testCreateChained() {
    $result = civicrm_api('HRJob', 'create', $this->fixtures['fullJob']);
    $this->assertAPISuccess($result);
    foreach ($result['values'] as $hrJobResult) {
      $this->assertEquals('Volunteer', $hrJobResult['contract_type']);

      $this->assertAPISuccess($hrJobResult['api.HRJobPay.create']);
      foreach ($hrJobResult['api.HRJobPay.create']['values'] as $hrJobPayResult) {
        $this->assertEquals(20, $hrJobPayResult['pay_amount']);
      }
      $this->assertAPISuccess($hrJobResult['api.HRJobHealth.create']);
      foreach ($hrJobResult['api.HRJobHealth.create']['values'] as $hrJobHealthResult) {
        $this->assertEquals('Family', $hrJobHealthResult['plan_type']);
        $this->assertEquals('A test Description', $hrJobHealthResult['description']);
      }
      $this->assertAPISuccess($hrJobResult['api.HRJobHour.create']);
      foreach ($hrJobResult['api.HRJobHour.create']['values'] as $hrJobHourResult) {
        $this->assertEquals('4', $hrJobHourResult['hours_type']);
        $this->assertEquals('40.00', $hrJobHourResult['hours_amount']);
        $this->assertEquals('Week', $hrJobHourResult['hours_unit']);
      }
      $this->assertAPISuccess($hrJobResult['api.HRJobPension.create']);
      foreach ($hrJobResult['api.HRJobPension.create']['values'] as $hrJobPensionResult) {
        $this->assertEquals(1, $hrJobPensionResult['is_enrolled']);
        $this->assertEquals(75.00, $hrJobPensionResult['er_contrib_pct']);
        $this->assertEquals(10.00, $hrJobPensionResult['ee_contrib_pct']);
      }

      //assert the creation of multiple job roles
      $roleValues = array(
        'title' => array('Manager', 'Senior Manager'),
        'region' => array('Asia', 'Europe'),
        'department' => array('Finance', 'HR'),
        'location' => array('Headquarters', 'Home'),
        'organization' => array('ABC Inc', 'XYZ Inc'),
      );
      $this->assertAPISuccess($hrJobResult['api.HRJobRole.create']);
      foreach ($hrJobResult['api.HRJobRole.create']['values'] as $hrJobRoleResult) {
        foreach ($roleValues as $name => $value) {
          $this->assertEquals($value[0], $hrJobRoleResult[$name]);
        }
      }
      $this->assertAPISuccess($hrJobResult['api.HRJobRole.create.1']);
      foreach ($hrJobResult['api.HRJobRole.create.1']['values'] as $hrJobRoleResult) {
        foreach ($roleValues as $name => $value) {
          $this->assertEquals($value[1], $hrJobRoleResult[$name]);
        }
      }

      //assert the creation of multiple leaves
      $this->assertAPISuccess($hrJobResult['api.HRJobLeave.create']);
      foreach ($hrJobResult['api.HRJobLeave.create']['values'] as $key => $hrJobLeaveResult) {
        $this->assertEquals(1, $hrJobLeaveResult['leave_type']);
        $this->assertEquals(10, $hrJobLeaveResult['leave_amount']);
      }
      $this->assertAPISuccess($hrJobResult['api.HRJobLeave.create.1']);
      foreach ($hrJobResult['api.HRJobLeave.create.1']['values'] as $key => $hrJobLeaveResult) {
        $this->assertEquals(3, $hrJobLeaveResult['leave_type']);
        $this->assertEquals(7, $hrJobLeaveResult['leave_amount']);
      }
    }
  }

  /**
   * Create two jobs (the first defaults to is_primary=1, the second defaults to is_primary=0).
   * Then switch the defaults.
   */
  public function testIsPrimary() {
    $cid = $this->individualCreate();

    // create first contact - defaults to is_primary=1
    $result1 = $this->callAPISuccess('HRJob', 'create', array(
      'contact_id' => $cid,
      'position' => 'First',
      'contract_type' => 'Volunteer',
    ));
    $this->assertEquals('1', $result1['values'][$result1['id']]['is_primary']);

    // create second contact - defaults to is_primary=0
    $result2 = $this->callAPISuccess('HRJob', 'create', array(
      'contact_id' => $cid,
      'position' => 'Second',
      'contract_type' => 'Employee - Permanent',
    ));
    $this->assertTrue(empty($result2['values'][$result2['id']]['is_primary']));

    // make sure first and second were both really persisted - and that
    // the value from first wasn't munged
    $this->assertDBQuery(
      1,
      'SELECT count(*) FROM civicrm_hrjob WHERE is_primary = 1 AND id = %1',
      array(1 => array($result1['id'], 'Integer'))
    );
    $this->assertDBQuery(
      1,
      'SELECT count(*) FROM civicrm_hrjob WHERE is_primary = 0 AND id = %1',
      array(1 => array($result2['id'], 'Integer'))
    );

    // switch around the is_primary
    $result2b = $this->callAPISuccess('HRJob', 'create', array(
      'id' => $result2['id'],
      'is_primary' => 1,
    ));
    $this->assertDBQuery(
      1,
      'SELECT count(*) FROM civicrm_hrjob WHERE is_primary = 1 AND id = %1',
      array(1 => array($result2['id'], 'Integer'))
    );
    $this->assertDBQuery(
      1,
      'SELECT count(*) FROM civicrm_hrjob WHERE is_primary = 0 AND id = %1',
      array(1 => array($result1['id'], 'Integer'))
    );

  }

  function testDuplicate() {
    $cid = $this->individualCreate();
    $original = $this->callAPISuccess('HRJob', 'create', $this->fixtures['fullJob'] + array('contact_id' => $cid));
    $duplicate = $this->callAPISuccess('HRJob', 'duplicate', array(
      'id' => $original['id'],
    ));
    $this->assertTrue(is_numeric($original['id']));
    $this->assertTrue(is_numeric($duplicate['id']));
    $this->assertTrue($original['id'] < $duplicate['id'], 'Duplicate ID should be newer than original ID');

    // Compare the main entity
    $originalGet = $this->callAPISuccess('HRJob', 'get', array('id' => $original['id'], 'sequential' => TRUE));
    $duplicateGet = $this->callAPISuccess('HRJob', 'get', array('id' => $duplicate['id'], 'sequential' => TRUE));
    $this->assertEqualsWithChanges($originalGet['values'], $duplicateGet['values'], array('id', 'is_primary'), 'HRJob: ');
    $this->assertEquals('1', $originalGet['values'][0]['is_primary']);
    $this->assertEquals('0', $duplicateGet['values'][0]['is_primary']);

    // Compare the child entities
    $subEntities = array(
      'HRJobPay',
      'HRJobHealth',
      'HRJobHour',
      'HRJobPension',
      'HRJobRole',
      'HRJobLeave',
      );
    foreach ($subEntities as $subEntity) {
      $originalGet = $this->callAPISuccess($subEntity, 'get', array('job_id' => $original['id'], 'sequential' => TRUE));
      $duplicateGet = $this->callAPISuccess($subEntity, 'get', array('job_id' => $duplicate['id'], 'sequential' => TRUE));
      $this->assertEqualsWithChanges($originalGet['values'], $duplicateGet['values'], array('id', 'job_id'), "$subEntity: ");
    }
  }

  /**
   * Ensure that pay_amount is nullable
   */
  function testNullPay() {
    $cid = $this->individualCreate();
    $hrJobResults = $this->callAPISuccess('HRJob', 'create', $this->fixtures['fullJob'] + array('contact_id' => $cid));
    $this->assertEquals(1, count($hrJobResults['values']));
    foreach ($hrJobResults['values'] as $hrJobResult) {
      $this->assertAPISuccess($hrJobResult['api.HRJobPay.create']);
      $this->assertEquals(1, count($hrJobResult['api.HRJobPay.create']['values']));
      foreach ($hrJobResult['api.HRJobPay.create']['values'] as $hrJobPayResult) {
        $this->assertEquals(20, $hrJobPayResult['pay_amount']);

        $setEmptyResult = $this->callAPISuccess('HRJobPay', 'create', array(
          'id' => $hrJobPayResult['id'],
          'pay_amount' => '',
        ));
        $this->assertDBQuery(1, "SELECT count(*) FROM civicrm_hrjob_pay WHERE id = %1 AND pay_amount IS NULL",
          array(1 => array($hrJobPayResult['id'], 'Integer'))
        );
      }
    }
  }

  //TODO check for length of employment field value

  function testDuplicateWithChange() {
    $cid = $this->individualCreate();
    $original = $this->callAPISuccess('HRJob', 'create', $this->fixtures['fullJob'] + array('contact_id' => $cid));
    $duplicate = $this->callAPISuccess('HRJob', 'duplicate', array(
      'id' => $original['id'],
      'title' => 'New title',
      'is_primary' => 1, // this will be the new primary
    ));
    $this->assertTrue(is_numeric($original['id']));
    $this->assertTrue(is_numeric($duplicate['id']));
    $this->assertTrue($original['id'] < $duplicate['id'], 'Duplicate ID should be newer than original ID');

    // Compare the main entity
    $originalGet = $this->callAPISuccess('HRJob', 'get', array('id' => $original['id'], 'sequential' => TRUE));
    $duplicateGet = $this->callAPISuccess('HRJob', 'get', array('id' => $duplicate['id'], 'sequential' => TRUE));
    $this->assertEqualsWithChanges($originalGet['values'], $duplicateGet['values'], array('id', 'is_primary', 'title'), 'HRJob: ');
    $this->assertEquals('1', $duplicateGet['values'][0]['is_primary']); // per $params
    $this->assertEquals('0', $originalGet['values'][0]['is_primary']); // implicitly flipped down to 0
    $this->assertEquals('New title', $duplicateGet['values'][0]['title']);
  }

  /**
   * Asser that two arrays include the same keys/values (notwithstanding $ignores)
   *
   * @param array $expected
   * @param array $actual
   * @param array $changedKeys list of keys to ignore
   */
  function assertEqualsWithChanges($expected, $actual, $changedKeys = array(), $prefix = '') {
    $expKeys = array_diff(array_keys($expected), $changedKeys);
    $actualKeys = array_diff(array_keys($actual), $changedKeys);
    sort($expKeys);
    sort($actualKeys);
    $this->assertEquals($expKeys, $actualKeys, "{$prefix}Expected and actual array keys should match");
    $this->assertTrue(!empty($expKeys));
    foreach ($expKeys as $expKey) {
      if (is_array($expected[$expKey]) && is_array($actual[$expKey])) {
        $this->assertEqualsWithChanges($expected[$expKey], $actual[$expKey], $changedKeys);
      } else {
        $this->assertEquals($expected[$expKey], $actual[$expKey], "{$prefix}Key [$expKey] should match");
      }
    }
    foreach ($changedKeys as $changedKey) {
      if (isset($expected[$changedKey]) || isset($actual[$changedKey])) {
        $this->assertNotEquals(@$expected[$changedKey], @$actual[$changedKey], "{$prefix}Key [$changedKey] should not match");
      }
    }
  }
}
