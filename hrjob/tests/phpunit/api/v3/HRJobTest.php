<?php

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
      'contract_type' => 'Volunteer',
      'api.HRJobPay.create' => array(
        'pay_amount' => 20,
      ),
      'api.HRJobHealth.create' => array(
        'plan_type' => 'Family',
        'description' => 'A test Description',
      ),
      'api.HRJobHour.create' => array(
        'hours_type' => 'part',
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
      'api.HRJobLeave.create' => array(
        'leave_type' => 'Annual',
        'leave_amount' => 10,
      ),
      'api.HRJobLeave.create.1' => array(
        'leave_type' => 'Sick',
        'leave_amount' => 7
      ),
    );
  }

  function tearDown() {
    parent::tearDown();
    $this->quickCleanup(array(
      'civicrm_hrjob',
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
        $this->assertEquals('part', $hrJobHourResult['hours_type']);
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
        $this->assertEquals('Annual', $hrJobLeaveResult['leave_type']);
        $this->assertEquals(10, $hrJobLeaveResult['leave_amount']);
      }
      $this->assertAPISuccess($hrJobResult['api.HRJobLeave.create.1']);
      foreach ($hrJobResult['api.HRJobLeave.create.1']['values'] as $key => $hrJobLeaveResult) {
        $this->assertEquals('Sick', $hrJobLeaveResult['leave_type']);
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
      'contract_type' => 'Employee',
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

  /**
   * A list of test-cases for the "initial join date" and "final termination date" fields.
   */
  function jobSummaryDateTestCases() {
    $cases = array();

    $cases[] = array(
      array(),
      array('start' => '', 'end' => '')
    );
    $cases[] = array(
      array(
        array('period_start_date' => '', 'period_end_date' => ''),
      ),
      array('start' => '', 'end' => '')
    );
    $cases[] = array(
      array(
        array('period_start_date' => '2012-01-02', 'period_end_date' => ''),
      ),
      array('start' => '2012-01-02 00:00:00', 'end' => '')
    );
    $cases[] = array(
      array(
        array('period_start_date' => '', 'period_end_date' => ''),
        array('period_start_date' => '2012-01-02', 'period_end_date' => ''),
        array('period_start_date' => '2011-05-01', 'period_end_date' => ''),
        array('period_start_date' => '2013-04-01', 'period_end_date' => ''),
      ),
      array('start' => '2011-05-01 00:00:00', 'end' => '')
    );
    $cases[] = array(
      array(
        array('period_start_date' => '', 'period_end_date' => '2012-01-02'),
      ),
      array('start' => '', 'end' => '2012-01-02 00:00:00')
    );
    $cases[] = array(
      array(
        array('period_start_date' => '', 'period_end_date' => '2011-09-02'),
        array('period_start_date' => '', 'period_end_date' => '2013-01-02'),
        array('period_start_date' => '', 'period_end_date' => '2012-08-02'),
        array('period_start_date' => '', 'period_end_date' => ''),
      ),
      array('start' => '', 'end' => '2013-01-02 00:00:00')
    );
    $cases[] = array(
      array(
        array('period_start_date' => '', 'period_end_date' => '2011-09-02'),
        array('period_start_date' => '', 'period_end_date' => '2013-01-02'),
        array('period_start_date' => '2009-08-05', 'period_end_date' => '2012-08-02'),
        array('period_start_date' => '2010-09-01', 'period_end_date' => ''),
      ),
      array('start' => '2009-08-05 00:00:00', 'end' => '2013-01-02 00:00:00')
    );
    return $cases;
  }

  /**
   * @dataProvider jobSummaryDateTestCases
   * @param array $jobFixtures list of API calls to make for creating the jobs
   * @param array $expectedDates list of job-summary values that are expected
   */
  function testJobSummaryDates($jobFixtures, $expectedDates) {
    // Make some noise to ensure we filter correctly
    $this->callAPISuccess('HRJob', 'create', array(
      'contact_id' => $this->individualCreate(array('email' => 'ignore1@example.com')),
      'period_start_date' => '2001-02-03',
      'period_end_date' => '2030-04-05',
    ));

    // Make the contact+jobs we care about
    $cid = $this->individualCreate();
    foreach ($jobFixtures as $jobFixture) {
      $this->callAPISuccess('HRJob', 'create', $jobFixture + array('contact_id' => $cid));
    }

    // Make some more noise
    $this->callAPISuccess('HRJob', 'create', array(
      'contact_id' => $this->individualCreate(array('email' => 'ignore2@example.com')),
      'period_start_date' => '2002-02-03',
      'period_end_date' => '2050-04-05',
    ));

    // Check the stats for the contact we care about
    $fields = hrjob_getSummaryFields(TRUE);
    $result = $this->callAPISuccess('contact', 'get', array(
      'id' => $cid,
      'return' => array($fields['Initial_Join_Date']['field'], $fields['Final_Termination_Date']['field']),
    ));
    $this->assertEquals(1, $result['count']);
    $this->assertTrue(
      isset(
      $expectedDates['start'],
      $result['values'][$result['id']][$fields['Initial_Join_Date']['field']],
      $expectedDates['end'],
      $result['values'][$result['id']][$fields['Final_Termination_Date']['field']]
      )
    );
    $this->assertEquals($expectedDates['start'], $result['values'][$result['id']][$fields['Initial_Join_Date']['field']], 'Compare Initial_Join_Date');
    $this->assertEquals($expectedDates['end'], $result['values'][$result['id']][$fields['Final_Termination_Date']['field']], 'Compare Final_Termination_Date');
  }

  // TODO test summary transitions

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
