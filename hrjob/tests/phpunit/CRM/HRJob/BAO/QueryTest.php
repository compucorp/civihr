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
 *  Include dataProvider for tests
 */
class CRM_HRJob_BAO_QueryTest extends CiviUnitTestCase {
  protected $_tablesToTruncate = array(
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
    $this->foreignKeyChecksOff();
    $this->quickCleanup($this->_tablesToTruncate);
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

