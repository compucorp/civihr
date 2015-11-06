<?php

require_once 'CRM/Core/DAO.php';
require_once 'CRM/Utils/Type.php';
class CRM_Appraisals_DAO_Appraisal extends CRM_Core_DAO
{
  /**
   * static instance to hold the table name
   *
   * @var string
   * @static
   */
  static $_tableName = 'civicrm_appraisal';
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
   * Unique Appraisal ID
   *
   * @var int unsigned
   */
  public $id;
  /**
   * Appraisal Cycle reference.
   *
   * @var int unsigned
   */
  public $appraisal_cycle_id;
  /**
   * Appraisal Contact ID.
   *
   * @var int unsigned
   */
  public $contact_id;
  /**
   * Appraisal Manager ID.
   *
   * @var int
   */
  public $manager_id;
  /**
   * Self Appraisal file reference.
   *
   * @var int
   */
  public $self_appraisal_file_id;
  /**
   * Manager Appraisal file reference.
   *
   * @var int
   */
  public $manager_appraisal_file_id;
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
  public $manager_appraisal_due;
  /**
   * Grade Due date.
   *
   * @var datetime
   */
  public $grade_due;
  /**
   * Is any individual due date changed?
   * 
   * @var bool
   */
  public $due_changed;
  /**
   * Meeting date.
   *
   * @var datetime
   */
  public $meeting_date;
  /**
   * Is meeting completed?
   *
   * @var bool
   */
  public $meeting_completed;
  /**
   * Is approved by employee?
   *
   * @var bool
   */
  public $approved_by_employee;
  /**
   * Current grade.
   *
   * @var int
   */
  public $grade;
  /**
   * Notes.
   *
   * @var string
   */
  public $notes;
  /**
   * Status ID.
   *
   * @var int
   */
  public $status_id;

  /**
   * class constructor
   *
   * @access public
   * @return civicrm_appraisal
   */
  function __construct()
  {
    $this->__table = 'civicrm_appraisal';
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
      self::$_links[] = new CRM_Core_Reference_Basic(self::getTableName() , 'appraisal_cycle_id', 'civicrm_appraisal_cycle', 'id');
      self::$_links[] = new CRM_Core_Reference_Basic(self::getTableName() , 'contact_id', 'civicrm_contact', 'id');
      self::$_links[] = new CRM_Core_Reference_Basic(self::getTableName() , 'manager_id', 'civicrm_contact', 'id');
      // TODO: self_appraisal_file_id
      // TODO: manager_appraisal_file_id
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
          'title' => ts('Appraisal ID') ,
          'required' => true,
          'import' => true,
          'where' => 'civicrm_appraisal.id',
          'headerPattern' => '',
          'dataPattern' => '',
          'export' => true,
        ) ,
        'appraisal_cycle_id' => array(
          'name' => 'appraisal_cycle_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Appraisal Cycle ID') ,
          'required' => true,
          'import' => true,
          'where' => 'civicrm_appraisal_cycle.id',
          'headerPattern' => '/(appraisal.)?cycle(.id$)/i',
          'dataPattern' => '',
          'export' => false,
          'FKClassName' => 'CRM_Appraisals_DAO_AppraisalCycle',
        ) ,
        'contact_id' => array(
          'name' => 'contact_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Contact ID') ,
          'import' => true,
          'where' => 'civicrm_appraisal.contact_id',
          'headerPattern' => '/(contact.)?id/i',
          'dataPattern' => '',
          'export' => true,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
        ) ,
        'manager_id' => array(
          'name' => 'manager_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Manager ID') ,
          'import' => true,
          'where' => 'civicrm_appraisal.manager_id',
          'headerPattern' => '/(manager.)?id/i',
          'dataPattern' => '',
          'export' => true,
          'FKClassName' => 'CRM_Contact_DAO_Contact',
        ) ,
        'self_appraisal_file_id' => array(
          'name' => 'self_appraisal_file_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Self Appraisal File ID') ,
          'import' => true,
          'where' => 'civicrm_appraisal.self_appraisal_file_id',
          'headerPattern' => '/self(.)?appraisal(.)?file(.)?id/i',
          'dataPattern' => '',
          'export' => true,
        ) ,
        'manager_appraisal_file_id' => array(
          'name' => 'manager_appraisal_file_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Manager Appraisal File ID') ,
          'import' => true,
          'where' => 'civicrm_appraisal.manager_appraisal_file_id',
          'headerPattern' => '/manager(.)?appraisal(.)?file(.)?id/i',
          'dataPattern' => '',
          'export' => true,
        ) ,
        'self_appraisal_due' => array(
          'name' => 'self_appraisal_due',
          'type' => CRM_Utils_Type::T_STRING,//T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Self Appraisal Due') ,
          'import' => true,
          'where' => 'civicrm_appraisal.self_appraisal_due',
          'headerPattern' => '/self.appraisal.due/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'Select Date',
          ) ,
        ) ,
        'manager_appraisal_due' => array(
          'name' => 'manager_appraisal_due',
          'type' => CRM_Utils_Type::T_STRING,//T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Manager Appraisal Due') ,
          'import' => true,
          'where' => 'civicrm_appraisal.manager_appraisal_due',
          'headerPattern' => '/manager.appraisal.due/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'Select Date',
          ) ,
        ) ,
        'grade_due' => array(
          'name' => 'grade_due',
          'type' => CRM_Utils_Type::T_STRING,//T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Grade Due') ,
          'import' => true,
          'where' => 'civicrm_appraisal.grade_due',
          'headerPattern' => '/grade.due/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'Select Date',
          ) ,
        ) ,
        'due_changed' => array(
          'name' => 'due_changed',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Is any individual due date changed?') ,
          'import' => true,
          'where' => 'civicrm_appraisal.due_changed',
          'headerPattern' => '/due.changed/i',
          'dataPattern' => '',
          'export' => true,
          'default' => '0',
          'html' => array(
            'type' => 'CheckBox',
          ) ,
        ) ,
        'meeting_date' => array(
          'name' => 'meeting_date',
          'type' => CRM_Utils_Type::T_DATE + CRM_Utils_Type::T_TIME,
          'title' => ts('Meeting Date') ,
          'import' => true,
          'where' => 'civicrm_appraisal.meeting_date',
          'headerPattern' => '/(meeting.)?date/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'Select Date',
          ) ,
        ) ,
        'meeting_completed' => array(
          'name' => 'meeting_completed',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Is meeting completed?') ,
          'import' => true,
          'where' => 'civicrm_appraisal.meeting_completed',
          'headerPattern' => '/(meeting.)?(completed?)/i',
          'dataPattern' => '',
          'export' => true,
          'default' => '1',
          'html' => array(
            'type' => 'CheckBox',
          ) ,
        ) ,
        'approved_by_employee' => array(
          'name' => 'approved_by_employee',
          'type' => CRM_Utils_Type::T_BOOLEAN,
          'title' => ts('Is approved by employee?') ,
          'import' => true,
          'where' => 'civicrm_appraisal.approved_by_employee',
          'headerPattern' => '/(approved.)?(by.)?(employee?)/i',
          'dataPattern' => '',
          'export' => true,
          'default' => '1',
          'html' => array(
            'type' => 'CheckBox',
          ) ,
        ) ,
        'grade' => array(
          'name' => 'grade',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Grade') ,
          'import' => true,
          'where' => 'civicrm_appraisal.grade',
          'headerPattern' => '/grade$/i',
          'dataPattern' => '',
          'export' => true,
        ) ,
        'notes' => array(
          'name' => 'notes',
          'type' => CRM_Utils_Type::T_TEXT,
          'title' => ts('Notes') ,
          'rows' => 8,
          'cols' => 60,
          'import' => true,
          'where' => 'civicrm_appraisal.notes',
          'headerPattern' => '/notes$/i',
          'dataPattern' => '',
          'export' => true,
          'html' => array(
            'type' => 'RichTextEditor',
          ) ,
        ) ,
        'status_id' => array(
          'name' => 'status_id',
          'type' => CRM_Utils_Type::T_INT,
          'title' => ts('Status ID') ,
          'import' => true,
          'where' => 'civicrm_appraisal.status_id',
          'headerPattern' => '/status(.)?id/i',
          'dataPattern' => '',
          'export' => true,
          'pseudoconstant' => array(
            'optionGroupName' => 'appraisal_status',
          )
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
        'appraisal_cycle_id' => 'appraisal_cycle_id',
        'contact_id' => 'contact_id',
        'manager_id' => 'manager_id',
        'self_appraisal_file_id' => 'self_appraisal_file_id',
        'manager_appraisal_file_id' => 'manager_appraisal_file_id',
        'self_appraisal_due' => 'self_appraisal_due',
        'manager_appraisal_due' => 'manager_appraisal_due',
        'grade_due' => 'grade_due',
        'due_changed' => 'due_changed',
        'meeting_date' => 'meeting_date',
        'meeting_completed' => 'meeting_completed',
        'approved_by_employee' => 'approved_by_employee',
        'grade' => 'grade',
        'notes' => 'notes',
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
            self::$_import['appraisal'] = & $fields[$name];
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
            self::$_export['appraisal'] = & $fields[$name];
          } else {
            self::$_export[$name] = & $fields[$name];
          }
        }
      }
    }
    return self::$_export;
  }
}
