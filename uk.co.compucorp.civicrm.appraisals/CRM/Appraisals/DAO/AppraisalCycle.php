<?php

require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_Appraisals_DAO_AppraisalCycle extends CRM_Core_DAO
{
  /**
   * static instance to hold the table name
   *
   * @var string
   * @static
   */
  static $_tableName = 'civicrm_appraisal_cycle';
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
   * Unique Appraisal Cycle ID
   *
   * @var int unsigned
   */
  public $id;
  /**
   * Appraisal name.
   *
   * @var varchar
   */
  public $name;
  /**
   * Cycle start date.
   *
   * @var datetime
   */
  public $cycle_start_date;
  /**
   * Cycle end date.
   *
   * @var datetime
   */
  public $cycle_end_date;
  /**
   * Self Appraisal Due date.
   *
   * @var datetime
   */
  public $self_appraisal_due;
  /**
   * Manager Appraisal Due date.
   *
   * @var datetime
   */
  public $manager_due;
  /**
   * Grade Appraisal Due date.
   *
   * @var datetime
   */
  public $grade_due;
  /**
   * Appraisal Cycle type ID.
   *
   * @var int
   */
  public $type_id;
  /**
   * Appraisal Cycle status ID.
   *
   * @var int
   */
  public $status_id;

  /**
   * class constructor
   *
   * @access public
   * @return civicrm_appraisal_cycle
   */
  function __construct()
  {
    $this->__table = 'civicrm_appraisal_cycle';
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
      self::$_links = static ::createReferenceColumns(__CLASS__);
      // TODO: type_id
      // TODO: status_id
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
          'title' => ts('Appraisal Cycle ID') ,
          'required' => true,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.id',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => true,
        ) ,
        'name' => array(
          'name' => 'name',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Appraisal Cycle name') ,
          'required' => true,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.name',
          'headerPattern' => '/name$/i',
          'dataPattern' => '',
          'export' => false,
        ) ,
        'cycle_start_date' => array(
          'name' => 'cycle_start_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Cycle Start Date') ,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.cycle_start_date',
          'headerPattern' => '/cycle.start.date$/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'Select Date',
          ) ,
        ) ,
        'cycle_end_date' => array(
          'name' => 'cycle_end_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Cycle End Date') ,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.cycle_end_date',
          'headerPattern' => '/cycle.end.date$/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'Select Date',
          ) ,
        ) ,
        'self_appraisal_due' => array(
          'name' => 'self_appraisal_due',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Self Appraisal Due') ,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.self_appraisal_due',
          'headerPattern' => '/self.appraisal.due/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'Select Date',
          ) ,
        ) ,
        'manager_due' => array(
          'name' => 'manager_due',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Manager Due') ,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.manager_due',
          'headerPattern' => '/manager.due/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'Select Date',
          ) ,
        ) ,
        'grade_due' => array(
          'name' => 'grade_due',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Grade Due') ,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.grade_due',
          'headerPattern' => '/grade.due/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'Select Date',
          ) ,
        ) ,
        'type_id' => array(
          'name' => 'type_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Type ID') ,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.type_id',
          'headerPattern' => '/type.id$/i',
          'dataPattern' => '',
          'export' => true,
        ) ,
        'status_id' => array(
          'name' => 'status_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Status ID') ,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.status_id',
          'headerPattern' => '/status.id$/i',
          'dataPattern' => '',
          'export' => true,
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
        'name' => 'name',
        'cycle_start_date' => 'cycle_start_date',
        'cycle_end_date' => 'cycle_end_date',
        'self_appraisal_due' => 'self_appraisal_due',
        'manager_due' => 'manager_due',
        'grade_due' => 'grade_due',
        'type_id' => 'type_id',
        'status_id' => 'status_id',
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
        if (CRM_Utils_Array::value('import', $field)) {
          if ($prefix) {
            self::$_import['appraisal_cycle'] = & $fields[$name];
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
        if (CRM_Utils_Array::value('export', $field)) {
          if ($prefix) {
            self::$_export['appraisal_cycle'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}
