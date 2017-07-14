<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_BAO_AbsenceType as AbsenceType;
use CRM_HRLeaveAndAbsences_BAO_LeaveBalanceChange as LeaveBalanceChange;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;

/**
 * Class to parse activity csv files.
 */
class CRM_HRLeaveAndAbsences_Import_Parser_Base extends CRM_HRLeaveAndAbsences_Import_Parser {

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
   * @var array
   *   Stores the type_id Balance change Option values.
   */
  private $balanceChangeTypes = [];

  /**
   * @var array
   *   Stores the date_type Leave request Option values.
   */
  private $dayTypes = [];

  /**
   * @var LeaveRequestCommentService
   */
  private $leaveRequestCommentService;

  /**
   * @var array
   */
  private $sicknessAbsenceTypes;

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
    $this->absenceStatuses = $this->getAbsenceStatuses();
  }

  /**
   * Handle the values in mapField mode.
   * This function does practically nothing because we don't need to do any validation
   * during the mapping field phase. However the base parser class calls this method
   * during MAPFIELD mode and this class needs to implement the method.
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
   * Each row represents a Leave request date with an absence_id property
   * that identifies leave dates belonging to the same parent leave request.
   * Information such as the start date and end date of the leave request is
   * replicated across all the individual leave request dates for the leave request.
   *
   * The Leave request is created from the first individual leave request date
   * encountered; Comments, Leave Dates for all the leave request dates and
   * balance change for that particular leave request date is also created.
   * Balance changes only will be created for subsequent rows of leave request
   * dates for the already created leave request.
   *
   * If there is an error while creating the leave request from the first leave
   * request date encountered, All other leave request dates will not be considered
   * and the error reported is reported for all the leave dates.
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
        'headerPattern' => '/^Qty/i',
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
      $fieldType = empty($fields[$key]['type']) ? null : $fields[$key]['type'];
      if ($fieldType && $fieldType == CRM_Utils_Type::T_DATE) {

        try {
          new DateTime($value);
        } catch (Exception $e) {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('Invalid Date value for '.$fields[$key]['title'], $errorMessage);
        }
      }

      if ($fieldType && ($fieldType == CRM_Utils_Type::T_FLOAT || $fieldType == CRM_Utils_Type::T_INT)) {
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
  private function validateOptionValues($params) {
    $errorMessage = NULL;
    $absenceType = $params['absence_type'];
    $absenceStatus = $params['status'];

    if (!isset($this->absenceStatuses[$absenceStatus])) {
      self::addToErrorMsg('Invalid Absence Status value', $errorMessage);
    }

    if (!isset($this->absenceTypes[$absenceType])) {
      self::addToErrorMsg('Invalid Absence type value', $errorMessage);
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
    $absenceTypes = AbsenceType::getEnabledAbsenceTypes();
    $absenceTypesList = [];

    foreach($absenceTypes as $absenceType) {
      $absenceTypesList[$absenceType->title] = $absenceType->id;
      if ($absenceType->allow_accruals_request) {
        $absenceTypesList[$absenceType->title. ' (Credit)'] = $absenceType->id;
      }
    }

    return $absenceTypesList;
  }

  /**
   * Build errorMessage by concatenating the error strings passed in.
   *
   * @param string $error
   *   A string containing error.
   * @param string $errorMessage
   *   A string containing all the error-fields, where the new errorName is concatenated.
   *
   */
  public static function addToErrorMsg($error, &$errorMessage) {
    if ($errorMessage) {
      $errorMessage .= "; $error";
    }
    else {
      $errorMessage = $error;
    }
  }

  /**
   * Returns the type_id Balance change Option values.
   *
   * @return array
   */
  private function getBalanceChangeTypes() {
    if (empty($this->balanceChangeTypes)) {
      $this->balanceChangeTypes = array_flip(LeaveBalanceChange::buildOptions('type_id', 'validate'));
    }

    return $this->balanceChangeTypes;
  }

  /**
   * Returns the date_type Leave request Option values.
   *
   * @return array
   */
  private function getDateTypes() {
    if (empty($this->dayTypes)) {
      $this->dayTypes = array_flip(LeaveRequest::buildOptions('from_date_type', 'validate'));
    }

    return $this->dayTypes;
  }

  /**
   * Returns the date_type Leave request Option values.
   *
   * @return LeaveRequestCommentService
   */
  private function getLeaveRequestCommentService() {
    if (empty($this->leaveRequestCommentService)) {
      $this->leaveRequestCommentService = new LeaveRequestCommentService();
    }

    return $this->leaveRequestCommentService;
  }

  /**
   * Returns Absence Types with is_sick as TRUE.
   *
   * @return array
   */
  private function getSicknessAbsenceTypes() {
    if (empty($this->sicknessAbsenceTypes)) {
      $sicknessAbsenceTypes = AbsenceType::getEnabledSicknessAbsenceTypes();
      $sicknessAbsenceTypesList = [];

      foreach($sicknessAbsenceTypes as $sicknessAbsenceType){
        $sicknessAbsenceTypesList[$sicknessAbsenceType->title] = $sicknessAbsenceType->id;
      }
      $this->sicknessAbsenceTypes = $sicknessAbsenceTypesList;
    }

    return $this->sicknessAbsenceTypes;
  }

  /**
   * Creates a leave request from the params array.
   *
   * @param array $params
   *
   * @return LeaveRequest
   */
  private function createLeaveRequestFromImportData($params) {
    $startDate = new DateTime($params['start_date']);
    $endDate = new DateTime($params['end_date']);
    $dateTypes = $this->getDateTypes();

    $payload = [
      'contact_id' => $params['contact_id'],
      'type_id' => $params['type_id'],
      'status_id' => $params['status_id'],
      'request_type' => LeaveRequest::REQUEST_TYPE_LEAVE,
      'from_date' => $startDate->format('YmdHis'),
      'to_date' => $endDate->format('YmdHis'),
      'from_date_type' => $dateTypes['all_day'],
      'to_date_type' => $dateTypes['all_day']
    ];

    if (strpos($params['absence_type'], '(Credit)')) {
      $payload['request_type'] = LeaveRequest::REQUEST_TYPE_TOIL;
      $payload['toil_to_accrue'] = $params['total_qty'];
      $payload['toil_duration'] = 60;
    }

    if (!empty($this->getSicknessAbsenceTypes()[$params['absence_type']])) {
      $payload['request_type'] = LeaveRequest::REQUEST_TYPE_SICKNESS;
      $payload['sickness_reason'] = 1;
    }

    return LeaveRequest::create($payload);
  }

  /**
   * Creates the balance change for the absence date in Params array.
   *
   * @param array $params
   * @param array $leaveDates
   *  array of CRM_HRLeaveAndAbsences_BAO_LeaveRequestDate
   */
  private function createBalanceChangeForLeaveDate($params, $leaveDates) {
    $absenceDate = new DateTime($params['absence_date']);
    $absenceDate = $absenceDate->format('Y-m-d');
    $balanceChangeTypes = $this->getBalanceChangeTypes();
    $balanceChangeType = $balanceChangeTypes['debit'];
    $amount = $params['qty'] * -1;

    if (strpos($params['absence_type'], '(Credit)')) {
      $balanceChangeType = $balanceChangeTypes['credit'];
      $amount = abs($amount);
    }

    foreach($leaveDates as $date) {
      if ($absenceDate == $date->date) {
        LeaveBalanceChange::create([
          'source_id' => $date->id,
          'source_type' => LeaveBalanceChange::SOURCE_LEAVE_REQUEST_DAY,
          'type_id' => $balanceChangeType,
          'amount' => $amount
        ]);
      }
    }
  }

  /**
   * Creates the comment for the given leave request ID with information from the
   * params array.
   *
   * @param array $params
   * @param int $leaveRequestID
   */
  private function createLeaveRequestComment($params, $leaveRequestID) {
    if (empty($params['comments'])) {
      return;
    }

    $absenceDate = new DateTime($params['absence_date']);
    $payload = [
      'leave_request_id' => $leaveRequestID,
      'text' => $params['comments'],
      'contact_id' => $params['contact_id'],
      'created_at' => $absenceDate->format('YmdHis'),
    ];

    $this->getLeaveRequestCommentService()->add($payload);
  }

  /**
   * Returns a list of the available leave request statuses.
   * This function also maps the absence statuses from the
   * old absence extension that has a different name from the one
   * on the L&A extension to the corresponding L&A status value.
   *
   * @return array
   */
  private function getAbsenceStatuses() {
    $absenceStatuses = array_flip(LeaveRequest::buildOptions('status_id'));
    $oldAbsenceStatusesMap = ['Requested' => $absenceStatuses['Awaiting Approval']];

    return array_merge($oldAbsenceStatusesMap, $absenceStatuses);
  }
}
