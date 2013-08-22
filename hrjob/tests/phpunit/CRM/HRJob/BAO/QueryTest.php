<?php
require_once 'CiviTest/CiviUnitTestCase.php';

/**
 *  Include dataProvider for tests
 */
class CRM_HRJob_BAO_QueryTest extends CiviUnitTestCase {
  function get_info() {
    return array(
      'name' => 'HRJob BAO Query',
      'description' => 'Test all HRJob_BAO_Query methods.',
      'group' => 'CiviCRM BAO Query Tests',
    );
  }

  public function dataProvider() {
    return new CRM_HRJob_BAO_QueryTestDataProvider;
  }

  function setUp() {
    parent::setUp();
  }

  function tearDown() {
    $tablesToTruncate = array(
      'civicrm_hrjob',
      'civicrm_hrjob_health',
      'civicrm_hrjob_hour',
      'civicrm_hrjob_leave',
      'civicrm_hrjob_pay',
      'civicrm_hrjob_pension',
      'civicrm_hrjob_role',
      'civicrm_email',
      'civicrm_contact',
     );
    $this->quickCleanup($tablesToTruncate);
  }

  /**
   *  Test CRM_Contact_BAO_Query::searchQuery()
   *  @dataProvider dataProvider
   */
  function testSearch($fv, $count, $ids, $full) {
    $op = new PHPUnit_Extensions_Database_Operation_Insert();
    $op->execute($this->_dbconn,
      new PHPUnit_Extensions_Database_DataSet_FlatXMLDataSet(
        dirname(__FILE__) . '/queryDataset.xml'
      )
    );

    $params = CRM_Contact_BAO_Query::convertFormValues($fv);
    $obj    = new CRM_Contact_BAO_Query($params);

    $obj->_useGroupBy = TRUE;

    $dao    = $obj->searchQuery();

    $contacts = array();
    while ($dao->fetch()) {
      $contacts[] = $dao->contact_id;
    }

    sort($contacts, SORT_NUMERIC);

    $this->assertEquals($ids, $contacts, 'In line ' . __LINE__);
  }
}

