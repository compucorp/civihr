<?php

use CRM_Hrjobcontract_ExportImportValuesConverter as ImportExportUtility;
use CRM_Hrjobcontract_BAO_HRJobContract as HRJobContract;
use CRM_Hrjobcontract_BAO_HRJobContractRevision as HRJobContractRevision;
use CRM_Hrjobcontract_Import_EntityHandler_HRJobLeave as HRJobLeaveHandler;

class CRM_Hrjobcontract_Import_Parser_EntitlementUpdate extends CRM_Hrjobcontract_Import_Parser_BaseClass {

  /**
   * @var array
   */
  protected $_entity = ['HRJobLeave'];

  /**
   * @var array
   *   Params for the current entity being prepared for the api.
   */
  protected $_params = [];

  /**
   * Sets the fields for the contract entitlement import.
   */
  public function setFields() {
    $this->_fields = array_merge(['do_not_import' => ['title' => ts('- do not import -')]], $this->getFields());
  }

  /**
   * handle the values in preview mode
   *
   * @param array $values
   *   The array of values belonging to this line
   *
   * @return boolean
   *   The result of this processing
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
   *   The result of this processing
   */
  public function summary(&$values) {
    $erroneousField = NULL;
    $this->setActiveFieldValues($values, $erroneousField);
    $params = &$this->getActiveFieldParams();
    $errorMessage = NULL;
    $errorMessage .= $this->validateRequiredFields($params);
    $errorMessage .= $this->validateFieldTypes($params);

    if (!empty($errorMessage)) {
      array_unshift($values, $errorMessage);
      return CRM_Import_Parser::ERROR;
    }

    return CRM_Import_Parser::VALID;
  }

  /**
   * Handle the values in import mode.
   *
   * The contract entitlements are updated for the latest revision of the contact's
   * current contract. The entitlements imported replaces whatever entitlements was
   * saved for the current contract before.
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
    $response = $this->summary($values);
    if ($response != CRM_Import_Parser::VALID) {
      return $response;
    }
    $params = &$this->getActiveFieldParams();

    try {
      $currentContract = HRJobContract::getCurrentContract($params['contact_id']);

      if (!$currentContract) {
        array_unshift($values, 'Contact does not have a valid contract');

        return CRM_Import_Parser::ERROR;
      }

      $contractRevision = new HRJobContractRevision();
      $contractRevision->jobcontract_id = $currentContract->id;
      $contractRevision->id = $currentContract->jobcontract_revision_id;

      $handler = new HRJobLeaveHandler();
      $previousRevision = [];
      $handler->handle($params, $contractRevision, $previousRevision);

    } catch(\RuntimeException $e) {
      array_unshift($values, $e->getMessage());

      return CRM_Import_Parser::ERROR;
    }

    return CRM_Import_Parser::VALID;
  }

  /**
   * Set import entity
   *
   * @param string $entity
   */
  public function setEntity($entity) {
    $this->_entity = $entity;
  }

  /**
   * Validate that all required fields are present and don't have empty values.
   *
   * @param array $params
   *
   * @return string
   */
  private function validateRequiredFields($params) {
    $errorMessages = NULL;

    foreach (self::getRequiredFields() as $key => $item) {
      if (empty($params[$key])) {
        self::addToErrorMessage("{$this->_fields[$key]->_title} is required", $errorMessages);
      }
    }

    return $errorMessages;
  }

  /**
   * Validate a row of values in CSV based on the field type.
   *
   * @param array $params
   *
   * @return string
   */
  private function validateFieldTypes($params) {
    $errorMessage = NULL;
    $fields = self::getFields();

    foreach($params as $key => $value) {
      $fieldType = empty($fields[$key]['type']) ? null : $fields[$key]['type'];

      if ($fieldType && $fieldType == CRM_Utils_Type::T_INT) {
        if(!is_numeric($value)) {
          self::addToErrorMsg('Invalid value for '.$fields[$key]['title'], $errorMessage);
        }
      }
    }

    return $errorMessage;
  }

  /**
   * Return the list of fields to be used by the job contract importer
   * in the Update Current Contract Entitlements mode.
   * The list consists of enabled absence types and the contactID field.
   *
   * @return array
   */
  private function getFields() {
    $importExportUtility = ImportExportUtility::singleton();
    $leaveTypes = $importExportUtility->getLeaveTypes();

    $fields['contact_id'] = [
      'name' => 'contact_id',
      'title' => ts('Contact ID'),
      'type' => CRM_Utils_Type::T_INT,
      'headerPattern' => '/Contact ID/i',
    ];

    $fields['- leave type amount fields -'] = ['title' => '- leave type amount fields -'];

    foreach($leaveTypes as $leaveType) {
      $title = $leaveType['title'];
      $key = filter_var($title, FILTER_SANITIZE_STRING);
      $fields[$key] = [
        'name' => $title,
        'title' => ts($title),
        'type' => CRM_Utils_Type::T_INT,
        'headerPattern' => "#$title#i",
      ];
    }

    return $fields;
  }

  /**
   * Returns the required fields for the import.
   *
   * @return array
   */
  public static function getRequiredFields() {
    return ['contact_id' => 'Contact ID'];
  }

  /**
   * Build errorMessage by concatenating the error strings passed in.
   *
   * @param string $error
   *   A string containing error.
   * @param string $errorMessage
   *   A string containing all the error-fields, where the new errorName
   *   is concatenated.
   */
  public static function addToErrorMessage($error, &$errorMessage) {
    if ($errorMessage) {
      $errorMessage .= "; $error";
    }
    else {
      $errorMessage = $error;
    }
  }
}
