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
/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 *
 */
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_Hrjobcontract_DAO_HRJobContractRevision extends CRM_Core_DAO
{
  /**
   * static instance to hold the table name
   *
   * @var string
   * @static
   */
  static $_tableName = 'civicrm_hrjobcontract_revision';
  /**
   * static instance to hold the field values
   *
   * @var array
   * @static
   */
  static $_fields = null;
  /**
   * static instance to hold the keys used in $_fields for each field.
   *
   * @var array
   * @static
   */
  static $_fieldKeys = null;
  /**
   * static instance to hold the FK relationships
   *
   * @var string
   * @static
   */
  static $_links = null;
  /**
   * static instance to hold the values that can
   * be imported
   *
   * @var array
   * @static
   */
  static $_import = null;
  /**
   * static instance to hold the values that can
   * be exported
   *
   * @var array
   * @static
   */
  static $_export = null;
  /**
   * static value to see if we should log any modifications to
   * this table in the civicrm_log table
   *
   * @var boolean
   * @static
   */
  static $_log = true;
  
  /**
   * Unique HRJob ID
   *
   * @var int unsigned
   */
  public $id;
  
  /**
   * FK to Contact
   *
   * @var int unsigned
   */
  public $contact_id;

  /**
   * class constructor
   *
   * @access public
   * @return civicrm_hrjob_hour
   */
  function __construct()
  {
    $this->__table = 'civicrm_hrjobcontract_revision';
    parent::__construct();
  }
  /**
   * return foreign keys and entity references
   *
   * @static
   * @access public
   * @return array of CRM_Core_Reference_Interface
   */
  static function getReferenceColumns()
  {
    if (!self::$_links) {
      self::$_links = array(
        new CRM_Core_Reference_Basic(self::getTableName() , 'jobcontract_id', 'civicrm_hrjobcontract', 'id') ,
      );
    }
    return self::$_links;
  }
  /**
   * returns all the column names of this table
   *
   * @access public
   * @return array
   */
  static function &fields()
  {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'id' => array(
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Revision Id') ,
          'required' => true,
          'export' => true,
          'import' => true,
          'where' => 'civicrm_hrjobcontract_revision.id',
          'headerPattern' => '/^revision\s?id/i',
        ) ,
        'jobcontract_id' => array(
          'name' => 'jobcontract_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Job Contract Id') ,
          'required' => false,
          'export' => true,
          'import' => true,
          'headerPattern' => '/(job\s?)?contract\s?id/i',
        ) ,
        'editor_uid' => array(
          'name' => 'editor_uid',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Editor UID') ,
          'required' => true,
          'export' => false,
          'import' => false,
        ) ,
        'created_date' => array(
          'name' => 'created_date',
          'type' => CRM_Utils_Type::T_DATE,
          'title' => ts('Created Date') ,
          'required' => false,
          'export' => true,
          'import' => true,
          'headerPattern' => '/^created\s?date/i',
        ) ,
        'effective_date' => array(
          'name' => 'effective_date',
          'type' => CRM_Utils_Type::T_DATE,
          'title' => ts('Effective Date') ,
          'required' => false,
          'export' => true,
          'import' => true,
          'headerPattern' => '/^effective\s?date/i',
        ) ,
        'change_reason' => array(
          'name' => 'change_reason',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Change reason') ,
          'required' => false,
          'export' => true,
          'import' => true,
          'pseudoconstant' => array(
            'optionGroupName' => 'hrjc_revision_change_reason',
          ),
          'headerPattern' => '/^change\s?reason/i',
        ) ,
        'modified_date' => array(
          'name' => 'modified_date',
          'type' => CRM_Utils_Type::T_DATE,
          'title' => ts('Modified Date') ,
          'required' => false,
          'export' => true,
          'import' => true,
          'headerPattern' => '/^modified\s?date/i',
        ) ,
        'status' => array(
          'name' => 'status',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Revision status') ,
          'required' => false,
          'export' => true,
          'import' => true,
          'headerPattern' => '/^revision\s?status/i',
        ) ,
        'details_revision_id' => array(
          'name' => 'details_revision_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Details Revision Id'),
          'required' => false,
          'export' => true,
          'import' => true,
          'FKClassName' => 'CRM_Hrjobcontract_DAO_HRJobDetails',
          'headerPattern' => '/^details\s?revision\s?id/i',
        ) ,
        'health_revision_id' => array(
          'name' => 'health_revision_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Health Revision Id'),
          'required' => false,
          'export' => true,
          'import' => true,
          'FKClassName' => 'CRM_Hrjobcontract_DAO_HRJobHealth',
          'headerPattern' => '/^health\s?revision\s?id/i',
        ) ,
        'hour_revision_id' => array(
          'name' => 'hour_revision_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Hour Revision Id'),
          'required' => false,
          'export' => true,
          'import' => true,
          'FKClassName' => 'CRM_Hrjobcontract_DAO_HRJobHour',
          'headerPattern' => '/^hour\s?revision\s?id/i',
        ) ,
        'leave_revision_id' => array(
          'name' => 'leave_revision_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Leave Revision Id'),
          'required' => false,
          'export' => true,
          'import' => true,
          'FKClassName' => 'CRM_Hrjobcontract_DAO_HRJobLeave',
          'headerPattern' => '/^leave\s?revision\s?id/i',
        ) ,
        'pay_revision_id' => array(
          'name' => 'pay_revision_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Pay Revision Id'),
          'required' => false,
          'export' => true,
          'import' => true,
          'FKClassName' => 'CRM_Hrjobcontract_DAO_HRJobPay',
          'headerPattern' => '/^pay\s?revision\s?id/i',
        ) ,
        'pension_revision_id' => array(
          'name' => 'pension_revision_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Pension Revision Id'),
          'required' => false,
          'export' => true,
          'import' => true,
          'FKClassName' => 'CRM_Hrjobcontract_DAO_HRJobPension',
          'headerPattern' => '/^pension\s?revision\s?id/i',
        ) ,
        'role_revision_id' => array(
          'name' => 'role_revision_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Role Revision Id'),
          'required' => false,
          'export' => true,
          'import' => true,
          'FKClassName' => 'CRM_Hrjobcontract_DAO_HRJobRole',
          'headerPattern' => '/^role\s?revision\s?id/i',
        ) ,
        'deleted' => array(
          'name' => 'deleted',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Is deleted?') ,
          'export' => false,
          'import' => false,
          'where' => 'civicrm_hrjobcontract_revision.deleted',
        ) ,
      );
    }
    return self::$_fields;
  }
  /**
   * Returns an array containing, for each field, the arary key used for that
   * field in self::$_fields.
   *
   * @access public
   * @return array
   */
  static function &fieldKeys()
  {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'id' => 'id',
        'jobcontract_id' => 'jobcontract_id',
        'editor_uid' => 'editor_uid',
        'created_date' => 'created_date',
        'modified_date' => 'modified_date',
        'effective_date' => 'effective_date',
        'change_reason' => 'change_reason',
        'status' => 'status',
        'details_revision_id' => 'details_revision_id',
        'health_revision_id' => 'health_revision_id',
        'hour_revision_id' => 'hour_revision_id',
        'leave_revision_id' => 'leave_revision_id',
        'pay_revision_id' => 'pay_revision_id',
        'pension_revision_id' => 'pension_revision_id',
        'role_revision_id' => 'role_revision_id',
        'deleted' => 'deleted',
      );
    }
    return self::$_fieldKeys;
  }
  /**
   * returns the names of this table
   *
   * @access public
   * @static
   * @return string
   */
  static function getTableName()
  {
    return self::$_tableName;
  }
  /**
   * returns if this table needs to be logged
   *
   * @access public
   * @return boolean
   */
  function getLog()
  {
    return self::$_log;
  }
  /**
   * returns the list of fields that can be imported
   *
   * @access public
   * return array
   * @static
   */
  static function &import($prefix = false)
  {
    if (!(self::$_import)) {
      self::$_import = array();
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (!empty($field['import'])) {
          if ($prefix) {
            self::$_import['hrjobcontract_revision'] = & $fields[$name];
          } else {
            self::$_import[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_import;
  }
  /**
   * returns the list of fields that can be exported
   *
   * @access public
   * return array
   * @static
   */
  static function &export($prefix = false)
  {
    if (!(self::$_export)) {
      self::$_export = array();
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (!empty($field['export'])) {
          if ($prefix) {
            self::$_export['hrjobcontract_revision'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}
