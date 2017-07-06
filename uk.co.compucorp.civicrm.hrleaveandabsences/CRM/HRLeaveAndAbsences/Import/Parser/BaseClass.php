<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;

/**
 * Class to parse activity csv files.
 */
class CRM_HRLeaveAndAbsences_Import_Parser_BaseClass extends CRM_HRLeaveAndAbsences_Import_Parser {

  use CRM_HRLeaveAndAbsences_Import_Helpers_DataImportTrait;

  /**
   * @var array
   */
  protected $_mapperKeys;

  /**
   * @var array
   */
  private $absenceTypes;

  /**
   * @var array
   */
  private $absenceStatuses;

  /**
   * @var array
   */
  private $leaveImportSuccess = [];

  /**
   * @var array
   */
  private $leaveImportError = [];

  /**
   * Class constructor.
   *
   * @param array $mapperKeys
   */
  public function __construct(&$mapperKeys) {
    parent::__construct();
    $this->_mapperKeys = &$mapperKeys;
  }

  /**
   * Function of undocumented functionality required by the interface.
   */
  protected function fini() {}

  /**
   * The initializer code, called before the processing.
   */
  public function init() {
    $activityContact = CRM_Activity_BAO_ActivityContact::import();
    $activityTarget['target_contact_id'] = $activityContact['contact_id'];

    $fields = self::getFields();
    $fields = ['' => ['title' => ts('- do not import -')]] + $fields;
    foreach ($fields as $name => $field) {
      $field['type'] = CRM_Utils_Array::value('type', $field);
      $field['dataPattern'] = CRM_Utils_Array::value('dataPattern', $field, '//');
      $field['headerPattern'] = CRM_Utils_Array::value('headerPattern', $field, '//');
      $this->addField($name, $field['title'], $field['type'], $field['headerPattern'], $field['dataPattern']);
    }

    $this->setActiveFields($this->_mapperKeys);
    $this->absenceTypes = $this->getAbsenceTypes();
    $this->absenceStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
  }

  /**
   * Handle the values in mapField mode.
   *
   * @param array $values
   *   The array of values belonging to this line.
   *
   * @return bool
   */
  public function mapField(&$values) {
    return CRM_Import_Parser::VALID;
  }

  /**
   * Handle the values in preview mode.
   *
   * @param array $values
   *   The array of values belonging to this line.
   *
   * @return bool
   *   the result of this processing
   */
  public function preview(&$values) {
    return $this->summary($values);
  }

  /**
   * Handle the values in summary mode.
   *
   * @param array $values
   *   The array of values belonging to this line.
   *
   * @return bool
   *   the result of this processing
   */
  public function summary(&$values) {
    $erroneousField = NULL;
    $this->setActiveFieldValues($values, $erroneousField);
    $params = &$this->getActiveFieldParams();
    $errorMessage = NULL;
    $errorMessage .= $this->validateFieldTypes($params);
    $errorMessage .= $this->validateOptionValues($params);

    if (!empty($errorMessage)) {
      array_unshift($values, $errorMessage);
      $errorMessage = NULL;
      return CRM_Import_Parser::ERROR;
    }

    return CRM_Import_Parser::VALID;
  }

  /**
   * Handle the values in import mode.
   *
   * @param int $onDuplicate
   *   The code for what action to take on duplicates.
   * @param array $values
   *   The array of values belonging to this line.
   *
   * @return bool
   *   the result of this processing
   */
  public function import($onDuplicate, &$values) {
    // First make sure this is a valid line
    $response = $this->summary($values);

    if ($response != CRM_Import_Parser::VALID) {
      return $response;
    }
    $params = &$this->getActiveFieldParams();

    $params['type_id'] = $this->absenceTypes[$params['absence_type']];
    $params['status_id'] = $this->absenceStatuses[$params['status']];
    $hasBeenImported = isset($this->leaveImportSuccess[$params['absence_id']]);
    $errorWhileImporting = isset($this->leaveImportError[$params['absence_id']]);

    $transaction = new CRM_Core_Transaction();
    if(!$hasBeenImported && !$errorWhileImporting) {
      try{
        $leaveRequest = $this->createLeaveRequestFromImportData($params);
        $this->createLeaveRequestComment($params, $leaveRequest->id);
        $this->leaveImportSuccess[$params['absence_id']] = $leaveRequest->getDates();
      }
      catch(Exception $e) {
        $this->leaveImportError[$params['absence_id']] = $e->getMessage();
        $transaction->rollback();
      }
    }

    if(isset($this->leaveImportError[$params['absence_id']])) {
      array_unshift($values, $this->leaveImportError[$params['absence_id']]);
      return CRM_Import_Parser::ERROR;
    }

    $this->createBalanceChangeForLeaveDate($params, $this->leaveImportSuccess[$params['absence_id']]);
    $transaction->commit();

    return CRM_Import_Parser::VALID;
  }

  /**
   * Gets available fields for import
   *
   * @return array
   */
  public static function getFields() {
    return [
      'contact_id' => [
        'name' => 'contact_id',
        'title' => ts('Contact ID'),
        'type' => CRM_Utils_Type::T_INT,
        'headerPattern' => '/Contact ID/i',
      ],
      'absence_id' => [
        'name' => 'absence_id',
        'title' => ts('Absence ID'),
        'type' => CRM_Utils_Type::T_INT,
        'headerPattern' => '/Absence ID/i',
      ],
      'absence_type' => [
        'name' => 'absence_type',
        'title' => ts('Absence Type'),
        'type' => CRM_Utils_Type::T_STRING,
        'headerPattern' => '/Absence Type/i',
      ],
      'absence_date' => [
        'name' => 'absence_date',
        'title' => ts('Absence Date'),
        'type' => CRM_Utils_Type::T_DATE,
        'headerPattern' => '/Absence Date/i',
      ],
      'qty' => [
        'name' => 'qty',
        'title' => ts('Qty'),
        'type' => CRM_Utils_Type::T_FLOAT,
        'headerPattern' => '/Qty/i',
      ],
      'start_date' => [
        'name' => 'start_date',
        'title' => ts('Start Date'),
        'type' => CRM_Utils_Type::T_DATE,
        'headerPattern' => '/Start Date/i',
      ],
      'end_date' => [
        'name' => 'end_date',
        'title' => ts('End Date'),
        'type' => CRM_Utils_Type::T_DATE,
        'headerPattern' => '/End Date/i',
      ],
      'total_qty' => [
        'name' => 'total_qty',
        'title' => ts('Total Qty'),
        'type' => CRM_Utils_Type::T_FLOAT,
        'headerPattern' => '/Total Qty/i',
      ],
      'status' => [
        'name' => 'status',
        'title' => ts('Status'),
        'type' => CRM_Utils_Type::T_STRING,
        'headerPattern' => '/Status/i',
      ],
      'comments' => [
        'name' => 'comments',
        'title' => ts('Comments'),
        'type' => CRM_Utils_Type::T_STRING,
        'headerPattern' => '/Comments/i',
      ],
    ];
  }

  /**
   * Validate a row of values in CSV based on the field type
   *
   * @param array $params
   *
   * @return string
   */
  private function validateFieldTypes($params){
    $errorMessage = NULL;
    $fields = self::getFields();

    foreach($params as $key=>$value) {
      if (isset($fields[$key]['type']) && $fields[$key]['type'] == CRM_Utils_Type::T_DATE) {

        try {
          new DateTime($value);
        } catch (Exception $e) {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('Invalid Date value for '.$fields[$key]['title'], $errorMessage);
        }
      }

      if (isset($fields[$key]['type']) && ($fields[$key]['type'] == CRM_Utils_Type::T_FLOAT || $fields[$key]['type'] == CRM_Utils_Type::T_INT)) {
        if(!is_numeric($value)) {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('Invalid value for '.$fields[$key]['title'], $errorMessage);
        }
      }
    }

    return $errorMessage;
  }

  /**
   * Validate that the Absence Type, Absence Status and any other option values in the csv row
   * are valid option values.
   *
   * @param array $params
   *
   * @return string
   */
  private function validateOptionValues($params){
    $errorMessage = NULL;
    $absenceType = $params['absence_type'];
    $absenceStatus = $params['status'];

    if (!isset($this->absenceStatuses[$absenceStatus])) {
      CRM_Contact_Import_Parser_Contact::addToErrorMsg('Invalid Absence Status value', $errorMessage);
    }

    if (!isset($this->absenceTypes[$absenceType])) {
      CRM_Contact_Import_Parser_Contact::addToErrorMsg('Invalid Absence type value', $errorMessage);
    }

    return $errorMessage;
  }

  /**
   * Retrieve available Absence types from the Absence Type table
   * Also for an absence type that allows accrual request, because the CSV imported does not
   * specify the request type but rather the absence type is listed with '(Credit) in front
   * to show that it is an accrual request. This is also simulated when creating the absence Types array to
   * to match what is in the CSV.
   *
   * @return array
   */
  private function getAbsenceTypes() {
    $absenceType = new AbsenceType();
    $absenceType->is_active = 1;
    $absenceType->find();

    $absenceTypes = [];
    while($absenceType->fetch()) {
      $absenceTypes[$absenceType->title] = $absenceType->id;
      if ($absenceType->allow_accruals_request) {
        $absenceTypes[$absenceType->title. ' (Credit)'] = $absenceType->id;
      }
    }

    return $absenceTypes;
  }
}
