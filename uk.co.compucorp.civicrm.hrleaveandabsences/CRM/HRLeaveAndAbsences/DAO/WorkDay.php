<?php
/*
+--------------------------------------------------------------------+
| CiviCRM version 4.7                                                |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2016                                |
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
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2016
 *
 * Generated from xml/schema/CRM/HRLeaveAndAbsences/WorkDay.xml
 * DO NOT EDIT.  Generated by CRM_Core_CodeGen
 */
require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_HRLeaveAndAbsences_DAO_WorkDay extends CRM_Core_DAO
{
  /**
   * static instance to hold the table name
   *
   * @var string
   */
  static $_tableName = 'civicrm_hrleaveandabsences_work_day';
  /**
   * static instance to hold the field values
   *
   * @var array
   */
  static $_fields = null;
  /**
   * static instance to hold the keys used in $_fields for each field.
   *
   * @var array
   */
  static $_fieldKeys = null;
  /**
   * static instance to hold the FK relationships
   *
   * @var string
   */
  static $_links = null;
  /**
   * static instance to hold the values that can
   * be imported
   *
   * @var array
   */
  static $_import = null;
  /**
   * static instance to hold the values that can
   * be exported
   *
   * @var array
   */
  static $_export = null;
  /**
   * static value to see if we should log any modifications to
   * this table in the civicrm_log table
   *
   * @var boolean
   */
  static $_log = true;
  /**
   * Unique WorkDay ID
   *
   * @var int unsigned
   */
  public $id;
  /**
   * A number between 1 and 7, following ISO-8601. 1 is Monday and 7 is Sunday
   *
   * @var int unsigned
   */
  public $day_of_the_week;
  /**
   * The type of this day, according to the values on the Work Day Type Option Group
   *
   * @var string
   */
  public $type;
  /**
   * The start time of this work day. This field is a char because CiviCRM can't handle TIME fields.
   *
   * @var string
   */
  public $time_from;
  /**
   * The end time of this work day. This field is a char because CiviCRM can't handle TIME fields.
   *
   * @var string
   */
  public $time_to;
  /**
   * The amount of break time (in hours) allowed for this day.
   *
   * @var float
   */
  public $break;
  /**
   * One of the values of the Leave Days Amount option group
   *
   * @var string
   */
  public $leave_days;
  /**
   * This is the number of hours between time_from and time_to minus break
   *
   * @var float
   */
  public $number_of_hours;
  /**
   * The Work Week this Day belongs to
   *
   * @var int unsigned
   */
  public $week_id;
  /**
   * class constructor
   *
   * @return civicrm_hrleaveandabsences_work_day
   */
  function __construct()
  {
    $this->__table = 'civicrm_hrleaveandabsences_work_day';
    parent::__construct();
  }
  /**
   * Returns foreign keys and entity references
   *
   * @return array
   *   [CRM_Core_Reference_Interface]
   */
  static function getReferenceColumns()
  {
    if (!self::$_links) {
      self::$_links = static ::createReferenceColumns(__CLASS__);
      self::$_links[] = new CRM_Core_Reference_Basic(self::getTableName() , 'week_id', 'civicrm_hrleaveandabsences_work_week', 'id');
    }
    return self::$_links;
  }
  /**
   * Returns all the column names of this table
   *
   * @return array
   */
  static function &fields()
  {
    if (!(self::$_fields)) {
      self::$_fields = array(
        'id' => array(
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => 'Unique WorkDay ID',
          'required' => true,
        ) ,
        'day_of_the_week' => array(
          'name' => 'day_of_the_week',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Day Of The Week') ,
          'description' => 'A number between 1 and 7, following ISO-8601. 1 is Monday and 7 is Sunday',
          'required' => true,
        ) ,
        'type' => array(
          'name' => 'type',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Type') ,
          'description' => 'The type of this day, according to the values on the Work Day Type Option Group',
          'required' => true,
          'maxlength' => 512,
          'size' => CRM_Utils_Type::HUGE,
          'pseudoconstant' => array(
            'optionGroupName' => 'hrleaveandabsences_work_day_type',
            'optionEditPath' => 'civicrm/admin/options/hrleaveandabsences_work_day_type',
          )
        ) ,
        'time_from' => array(
          'name' => 'time_from',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Time From') ,
          'description' => 'The start time of this work day. This field is a char because CiviCRM can\'t handle TIME fields.',
          'maxlength' => 5,
          'size' => CRM_Utils_Type::SIX,
        ) ,
        'time_to' => array(
          'name' => 'time_to',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Time To') ,
          'description' => 'The end time of this work day. This field is a char because CiviCRM can\'t handle TIME fields.',
          'maxlength' => 5,
          'size' => CRM_Utils_Type::SIX,
        ) ,
        'break' => array(
          'name' => 'break',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => ts('Break') ,
          'description' => 'The amount of break time (in hours) allowed for this day. ',
          'precision' => array(
            20,
            2
          ) ,
        ) ,
        'leave_days' => array(
          'name' => 'leave_days',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Leave Days') ,
          'description' => 'One of the values of the Leave Days Amount option group',
          'maxlength' => 512,
          'size' => CRM_Utils_Type::HUGE,
          'pseudoconstant' => array(
            'optionGroupName' => 'hrleaveandabsences_leave_days_amounts',
            'optionEditPath' => 'civicrm/admin/options/hrleaveandabsences_leave_days_amounts',
          )
        ) ,
        'number_of_hours' => array(
          'name' => 'number_of_hours',
          'type' => CRM_Utils_Type::T_MONEY,
          'title' => ts('Number Of Hours') ,
          'description' => 'This is the number of hours between time_from and time_to minus break',
          'precision' => array(
            20,
            2
          ) ,
        ) ,
        'week_id' => array(
          'name' => 'week_id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => 'The Work Week this Day belongs to',
          'required' => true,
          'FKClassName' => 'CRM_HRLeaveAndAbsences_DAO_WorkWeek',
        ) ,
      );
    }
    return self::$_fields;
  }
  /**
   * Returns an array containing, for each field, the arary key used for that
   * field in self::$_fields.
   *
   * @return array
   */
  static function &fieldKeys()
  {
    if (!(self::$_fieldKeys)) {
      self::$_fieldKeys = array(
        'id' => 'id',
        'day_of_the_week' => 'day_of_the_week',
        'type' => 'type',
        'time_from' => 'time_from',
        'time_to' => 'time_to',
        'break' => 'break',
        'leave_days' => 'leave_days',
        'number_of_hours' => 'number_of_hours',
        'week_id' => 'week_id',
      );
    }
    return self::$_fieldKeys;
  }
  /**
   * Returns the names of this table
   *
   * @return string
   */
  static function getTableName()
  {
    return self::$_tableName;
  }
  /**
   * Returns if this table needs to be logged
   *
   * @return boolean
   */
  function getLog()
  {
    return self::$_log;
  }
  /**
   * Returns the list of fields that can be imported
   *
   * @param bool $prefix
   *
   * @return array
   */
  static function &import($prefix = false)
  {
    if (!(self::$_import)) {
      self::$_import = array();
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (CRM_Utils_Array::value('import', $field)) {
          if ($prefix) {
            self::$_import['hrleaveandabsences_work_day'] = & $fields[$name];
          } else {
            self::$_import[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_import;
  }
  /**
   * Returns the list of fields that can be exported
   *
   * @param bool $prefix
   *
   * @return array
   */
  static function &export($prefix = false)
  {
    if (!(self::$_export)) {
      self::$_export = array();
      $fields = self::fields();
      foreach($fields as $name => $field) {
        if (CRM_Utils_Array::value('export', $field)) {
          if ($prefix) {
            self::$_export['hrleaveandabsences_work_day'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}
