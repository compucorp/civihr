<?php

require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_Hrjobcontract_DAO_PayScale extends CRM_Core_DAO
{
  /**
   * static instance to hold the table name
   *
   * @var string
   * @static
   */
  static $_tableName = 'civicrm_hrpay_scale';
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
   * Unique Pay Scale type ID
   *
   * @var int unsigned
   */
  public $id;
  /**
   *
   * @var int
   */
  public $pay_scale;
  /**
   *
   * @var string
   */
  public $pay_grade;
  /**
   *
   * @var string
   */
  public $currency;
  /**
   *
   * @var int
   */
  public $amount;
  /**
   *
   * @var string
   */
  public $periodicity;
  /**
   *
   * @var boolean
   */
  public $is_active;
  /**
   * class constructor
   *
   * @access public
   * @return civicrm_pay_scale
   */
  function __construct()
  {
    $this->__table = 'civicrm_hrpay_scale';
    parent::__construct();
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
          'title' => ts('Pay Scale Id') ,
          'required' => true,
        ) ,
        'pay_scale' => array(
          'name' => 'pay_scale',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Pay Scale') ,
          'maxlength' => 63,
          'export' => true,
          'where' => 'civicrm_hrpay_scale.pay_scale',
          'headerPattern' => '',
          'dataPattern' => '',
        ) ,
        'pay_grade' => array(
          'name' => 'pay_grade',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Pay Grade') ,
          'maxlength' => 63,
          'export' => true,
          'where' => 'civicrm_hrpay_scale.pay_grade',
          'headerPattern' => '',
          'dataPattern' => '',
        ) ,
        'currency' => array(
          'name' => 'currency',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Currency') ,
          'maxlength' => 63,
          'export' => true,
          'where' => 'civicrm_hrpay_scale.currency',
          'headerPattern' => '',
          'dataPattern' => '',
          'pseudoconstant' => array(
            'optionGroupName' => 'currencies_enabled',
          )
        ) ,
        'amount' => array(
          'name' => 'amount',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Amount') ,
          'export' => true,
          'where' => 'civicrm_hrpay_scale.amount',
          'headerPattern' => '',
          'dataPattern' => '',
        ) ,
        'periodicity' => array(
          'name' => 'periodicity',
          'type' => CRM_Utils_Type::T_STRING,
          'title' => ts('Periodicity') ,
          'maxlength' => 63,
          'export' => true,
          'where' => 'civicrm_hrpay_scale.periodicity',
          'headerPattern' => '',
          'dataPattern' => '',
        ) ,
        'is_active' => array(
          'name' => 'is_active',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Pay Scale Is Active') ,
          'default' => '1',
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
        'pay_scale' => 'pay_scale',
        'pay_grade' => 'pay_grade',
        'currency' => 'currency',
        'amount' => 'amount',
        'periodicity' => 'periodicity',
        'is_active' => 'is_active',
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
            self::$_import['pay_scale'] = & $fields[$name];
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
            self::$_export['pay_scale'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}
